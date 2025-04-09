import requests
from bs4 import BeautifulSoup as bs
from decimal import Decimal, ROUND_HALF_UP
import re
import sqlite3
import stores
import history

def fetch_page_content(url):
    r = requests.get(url).text
    soup = bs(r, "html.parser")
    return soup

def scrape_alkoutlet():
    for url in stores.AlkOutlet_urls:
        current_page = url

        while current_page:
            print(f"Scraping: {current_page}")

            soup = fetch_page_content(current_page)
            products = soup.find_all("li", class_="item product product-item")

            for product in products:
                title = product.find("a", class_="product-item-link").string.strip()

                price_tag = product.find("span", class_="price")
                cena = Decimal(price_tag.string.replace(" €", "").replace(",", ".").strip()) if price_tag else None

                tilpums_raw = product.find("div", class_="product-item-attributes")
                if tilpums_raw:
                    tilpums_match = re.search(r"(\d+(?:\.\d*)?)\s*(ml|l)", tilpums_raw.string.replace(" ", ""), re.IGNORECASE)
                    if tilpums_match:
                        quantity = Decimal(tilpums_match.group(1).replace(",", "."))
                        unit = tilpums_match.group(2).lower()

                        if unit == "ml" or unit =="ML":
                            quantity = quantity / 1000
                        tilpums = quantity.quantize(Decimal("0.01"), rounding=ROUND_HALF_UP)

                        percent_match = re.search(r"(\d+(?:.\d*)?)\s*%", title, re.IGNORECASE)
                        if percent_match:
                            percentage = percent_match.group(0)
                            title = title.replace(percentage, "").strip()
                    else:
                        tilpums = None
                else:
                    tilpums = None

                link = product.find("a", class_="product photo product-item-photo")["href"]
                link = str(link)

                if tilpums and cena > 0:
                    cenaL = float((cena / tilpums).quantize(Decimal("0.01"), rounding=ROUND_HALF_UP))
                else:
                    cenaL = None
                try:
                    pattern = r"(?<!\w)(" + "|".join(re.escape(word) for word in stores.words_to_remove) + r")(?!\w)"
                    cleaned_title = re.sub(pattern, "", title)
                    title = re.sub(r"\s+", " ", cleaned_title).strip()
                except Exception as e:
                    print(f"An error occurred: {e}")

                try:
                    kategorija = soup.find("span", class_="base").string.strip()
                    if "Sula, nektārs, smūtiji" in kategorija:
                        kategorija = "Sulas"
                    if "Ūdens, minerālūdens" in kategorija:
                        kategorija = "Ūdens"
                except AttributeError:
                    kategorija = None

                c.execute("""
                    INSERT INTO Kabinets (Name, Volume, Price, Store, Category, PricePerLiter, links)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                """, (title, float(tilpums) if tilpums else None, float(cena) if cena else None, "AlkOutlet", kategorija, float(cenaL) if cenaL else None, link))

            conn.commit()

            next_page = get_next_page(soup)
            current_page = next_page
def get_next_page(soup):
    page = soup.find("ul", {"class": "items pages-items"})
    if page:
        next_page_li = page.find("li", {"class": "item pages-item-next"})
        if next_page_li and next_page_li.find("a"):
            return next_page_li.find("a")["href"]
    return None

def scrape_rimi():
    for url in stores.rimi_urls:
        current_page = url

        while current_page:
            print(f"Scraping: {current_page}")

            soup = fetch_page_content(current_page)

            products = soup.find_all("li", class_="product-grid__item")

            for product in products:
                title = product.find("p", class_="card__name").string.strip()

                price_div = product.find("div", class_="price-tag card__price")
                try:
                    whole_number = price_div.find("span").string.strip()
                    cents = price_div.find("sup").string.strip()
                    cena = Decimal(f"{whole_number}.{cents}")
                except AttributeError:
                    cena = None

                link = product.find("a", class_="card__url js-gtm-eec-product-click")["href"]
                link = "https://www.rimi.lv"+str(link)

                tilpums_raw = product.find("p", class_="card__name")
                if tilpums_raw:
                    tilpums_match = re.search(r"(\d+(?:[,.]\d*)?)\s*(ml|l)(?!\w)", tilpums_raw.string.replace(" ", ""), re.IGNORECASE)
                    if tilpums_match:
                        quantity = Decimal(tilpums_match.group(1).replace(",", "."))
                        unit = tilpums_match.group(2).lower()

                        volume = tilpums_match.group(0)
                        title = title.replace(volume, "").strip()

                        percent_match = re.search(r"(\d+(?:.\d*)?)\s*%", title, re.IGNORECASE)
                        if percent_match:
                            percentage = percent_match.group(0)
                            title = title.replace(percentage, "").strip()

                        if unit == "ml":
                            quantity = quantity / 1000
                        tilpums = quantity.quantize(Decimal("0.01"), rounding=ROUND_HALF_UP)
                    else:
                        tilpums = None
                else:
                    tilpums = None

                try:
                    cenaL = (cena / tilpums).quantize(Decimal("0.01"), rounding=ROUND_HALF_UP)
                except TypeError:
                    cenaL = None
                except ZeroDivisionError:
                    cenaL = None

                try:
                    pattern = r"(?<!\w)(" + "|".join(re.escape(word) for word in stores.words_to_remove) + r")(?!\w)"
                    cleaned_title = re.sub(pattern, "", title)
                    title = re.sub(r"\s+", " ", cleaned_title).strip()

                except Exception as e:
                    print(f"An error occurred: {e}")

                text = current_page
                kategorija = next((value for word, value in stores.word_mapping.items() if word in text), None)

                c.execute("""
                    INSERT INTO Kabinets (Name, Volume, Price, Store, Category, PricePerLiter, links)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                """, (title, float(tilpums) if tilpums else None, float(cena) if cena else None, "Rimi", kategorija, float(cenaL) if cenaL else None, link))
            conn.commit()
            next_page = get_next_page_rimi(soup)
            current_page = next_page

def get_next_page_rimi(soup):
    page = soup.find("ul", class_="pagination__list")
    if page:
        next_page_li = page.find_all("li", class_="pagination__item -chevron")
        next_page_li = next_page_li[-1]
        if next_page_li and next_page_li.find("a"):
            return "https://www.rimi.lv"+str(next_page_li.find("a")["href"])
    return None

def scrape_SandW():
    for url in stores.SandWine_urls:
        current_page = url

        while current_page:
            print(f"Scraping: {current_page}")

            soup = fetch_page_content(current_page)
            products = soup.find_all("div", class_="col mb-3")

            for product in products:
                title = product.find("h2", class_="product-title").string.strip()

                price_div = product.find("div", class_="product-price-sale")
                try:
                    first_price = price_div.find(string=True, recursive=False).strip()
                    cena = Decimal(first_price.replace(" €", ""))
                except AttributeError:
                    cena = None

                tilpums_raw = product.find("div", class_="product-details")
                if tilpums_raw:
                    tilpums_match = re.search(r"(\d+(?:\.\d*)?)\s*(ml|l)", tilpums_raw.string.replace(" ", ""), re.IGNORECASE)
                    if tilpums_match:
                        quantity = Decimal(tilpums_match.group(1).replace(",", "."))
                        unit = tilpums_match.group(2).lower()

                        if unit == "ml":
                            quantity = quantity / 1000
                        tilpums = quantity.quantize(Decimal("0.01"), rounding=ROUND_HALF_UP)
                    else:
                        tilpums = None
                else:
                    tilpums = None

                kategorija = product.find("div", class_="product-details").string.strip()
                if ',' in kategorija:
                    kategorija = kategorija.split(',', 1)[0]
                else:
                    kategorija = None

                try:
                    cenaL = float((cena / tilpums).quantize(Decimal("0.01"), rounding=ROUND_HALF_UP))
                except TypeError:
                    cena = None

                link = product.find("a", class_="text-decoration-none text-dark d-block mb-auto")["href"]
                link = "https://www.spiritsandwine.lv"+str(link)

                c.execute("""
                    INSERT INTO Kabinets (Name, Volume, Price, Store, Category, PricePerLiter, links)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                """, (title, float(tilpums) if tilpums else None, float(cena) if cena else None, "Spirits & Wine", kategorija, float(cenaL) if cenaL else None, link))

            conn.commit()

            next_page = get_next_page_SandW(soup)
            current_page = next_page

def get_next_page_SandW(soup):
    pagination_div = soup.find("div", class_="pagination")
    if pagination_div:
        next_page_a = pagination_div.find("a", class_="btn-next")
        if next_page_a and "href" in next_page_a.attrs:
            return "https://www.spiritsandwine.lv"+str(next_page_a["href"])
    return None

def scrape_LB():
    for url in stores.LB_urls:
        current_page = url

        while current_page:
            print(f"Scraping: {current_page}")

            soup = fetch_page_content(current_page)
            products = soup.find_all("li", class_="item product product-item")

            for product in products:
                title = product.find("a", class_="product-item-link").string.strip()

                price_div = product.find_all("span", class_="price")
                if len(price_div) >= 2:
                    cena = price_div[1].get_text(strip=True)
                    cena = Decimal(cena.replace(" €", "").replace(",", "."))
                elif  len(price_div) == 1:
                    cena = product.find("span", class_="price").string.strip()
                    cena = Decimal(cena.replace(" €", "").replace(",", "."))
                else:
                    cena = None

                tilpums_raw = product.find("div", class_="product-additional-attributes")
                if tilpums_raw:
                    tilpums_match = re.search(r"(\d+(?:\.\d*)?)\s*(ml|l)", tilpums_raw.string.replace(" ", ""), re.IGNORECASE)
                    if tilpums_match:
                        quantity = Decimal(tilpums_match.group(1).replace(",", "."))
                        unit = tilpums_match.group(2).lower()

                        if unit == "ml":
                            quantity = quantity / 1000
                        tilpums = quantity.quantize(Decimal("0.01"), rounding=ROUND_HALF_UP)
                    else:
                        tilpums = None
                else:
                    tilpums = None

                try:
                    cenaL = float((cena / tilpums).quantize(Decimal("0.01"), rounding=ROUND_HALF_UP))
                except TypeError:
                    cenaL = None

                kategorija = soup.find("ul", class_="items")
                try:
                    kategorija = kategorija.find("strong").string.strip()
                    if "Balzams" in kategorija:
                        kategorija = "Balzāms"
                except AttributeError:
                    kategorija = None

                link = product.find("a", class_="product-item-link")["href"]
                link = str(link)

                c.execute("""
                    INSERT INTO Kabinets (Name, Volume, Price, Store, Category, PricePerLiter, links)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                """, (title, float(tilpums) if tilpums else None, float(cena) if cena else None, "Latvijas Balzāms", kategorija, float(cenaL) if cenaL else None, link))

            conn.commit()

            next_page = get_next_page_LB(soup)
            current_page = next_page

def get_next_page_LB(soup):
    page = soup.find("ul", class_="items pages-items")
    if page:
        next_page_li = page.find("li", {"class": "item pages-item-next"})
        if next_page_li and next_page_li.find("a"):
            return next_page_li.find("a")["href"]
    return None

def update_categories(c):
    c.execute("UPDATE Kabinets SET Category = 'Balzāms' WHERE Category = 'Balzams';")
    c.execute("UPDATE Kabinets SET Category = 'Enerģijas dzērieni' WHERE Category = 'Enerģijas dzēriens';")
    c.execute("UPDATE Kabinets SET Category = 'Kokteiļi' WHERE Category = 'Bezalkoholiskie kokteiļi';")
    c.execute("UPDATE Kabinets SET Category = 'Konjaks' WHERE Category = 'Konjaki';")
    c.execute("UPDATE Kabinets SET Category = 'Limonāde' WHERE Category = 'Gāzēts limonāde';")
    c.execute("UPDATE Kabinets SET Category = 'Limonādes' WHERE Category = 'Limonāde';")
    c.execute("UPDATE Kabinets SET Category = 'Portvīns' WHERE Category = 'Portvīns / šerijs';")
    c.execute("UPDATE Kabinets SET Category = 'Sarkanvīns' WHERE Category = 'Bag-in-box sarkanvīns';")
    c.execute("UPDATE Kabinets SET Category = 'Sārtvīns' WHERE Category = 'Bag-in-box sārtvīns';")
    c.execute("UPDATE Kabinets SET Category = 'Sidrs' WHERE Category = 'Bezalkoholiskais sidrs';")
    c.execute("UPDATE Kabinets SET Category = 'Sula' WHERE Category = 'Sulas';")
    c.execute("UPDATE Kabinets SET Category = 'Sīrupi' WHERE Category = 'Sīrupi / piedevas kokteiļiem';")
    c.execute("UPDATE Kabinets SET Category = 'Tēja' WHERE Category = 'Tējas dzērieni';")
    c.execute("UPDATE Kabinets SET Category = 'Uzlējumi' WHERE Category = 'Uzlējums';")
    c.execute("UPDATE Kabinets SET Category = 'Vermuts' WHERE Category = 'Vermuts / aperitīvs';")
    c.execute("UPDATE Kabinets SET Category = 'Vīns' WHERE Category = 'Augļu vīns';")
    c.execute("UPDATE Kabinets SET Category = 'Vīns' WHERE Category = 'Bezalkoholiskais dzirkstošais vīns';")
    c.execute("UPDATE Kabinets SET Category = 'Vīns' WHERE Category = 'Dzirkstošais vīns';")
    c.execute("UPDATE Kabinets SET Category = 'Vīns' WHERE Category = 'Rozā vīns';")
    c.execute("UPDATE Kabinets SET Category = 'Vīns' WHERE Category = 'Stiprināts vīns';")
    c.execute("UPDATE Kabinets SET Category = 'Karstvīns' WHERE Category = 'Karstvīns / karstie dzērieni';")
    c.execute("UPDATE Kabinets SET Category = 'Vīns' WHERE Category = 'Bag-in-box vīns';")
    c.execute("UPDATE Kabinets SET Category = 'Kokteilis' WHERE Category = 'Bezalkoholiskais kokteilis';")
    c.execute("UPDATE Kabinets SET Category = 'Vīns' WHERE Category = 'Vīni un dzirkstošie';")
    c.execute("UPDATE Kabinets SET Category = 'Kokteiļi' WHERE Category = 'Kokteilis';")
    conn.commit()
if __name__ == "__main__":
    conn = sqlite3.connect("/var/www/mysite/database/kabinets.db")
    c = conn.cursor()

    c.execute("DELETE FROM Kabinets")
    conn.commit()

    scrape_alkoutlet()
    scrape_rimi()
    scrape_SandW()
    scrape_LB()
    update_categories(c)
    
    history.update_categories()

    c.execute(f"SELECT COUNT(*) FROM kabinets")
    row_count = c.fetchone()[0]

    print(f"Scraped {row_count} products")

    conn.close()

    import subprocess
    subprocess.run(["php", "/var/www/user.happyhour.lat/log_prices.php"])
    subprocess.run(["php", "/var/www/user.happyhour.lat/finalize_prices.php"])