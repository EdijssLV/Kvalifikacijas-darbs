import requests
from bs4 import BeautifulSoup as bs
from decimal import Decimal, ROUND_HALF_UP
import re
import sqlite3

def fetch_page_content(url):
    r = requests.get(url).text
    soup = bs(r, "html.parser")
    return soup

def scrape_alkoutlet():
    shop_urls = [
        "https://alkoutlet.lv/stiprie.html?p=1&product_list_limit=36",
        "https://alkoutlet.lv/vins-un-vina-dzerieni.html?p=1&product_list_limit=36",
        "https://alkoutlet.lv/alus-sidri-kokteili.html?p=1&product_list_limit=36",
        "https://alkoutlet.lv/bezalkoholiskie.html?p=1&product_list_limit=36",
    ]

    for url in shop_urls:
        current_page = url

        while current_page:  # Loop until there are no more pages
            print(f"Scraping: {current_page}")  # Show current page being scraped

            soup = fetch_page_content(current_page)  # Fetch the page
            products = soup.find_all("li", class_="item product product-item")  # Extract products

            for product in products:
                # Extract product name
                title = product.find("a", class_="product-item-link").string.strip()

                # Extract and clean price
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

                        percent_match = re.search(r"(\d+(?:,\d*)?)\s*%", title, re.IGNORECASE)
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

                c.execute("""
                    INSERT INTO Kabinets (Name, Volume, Price, Store, Category, PricePerLiter, links)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                """, (title, float(tilpums) if tilpums else None, float(cena) if cena else None, "AlkOutlet", None, float(cenaL) if cenaL else None, link))

            conn.commit()

            # Get the next page URL
            next_page = get_next_page(soup)
            current_page = next_page  # Update for the next loop iteration

def get_next_page(soup):
    page = soup.find("ul", {"class": "items pages-items"})
    if page:
        next_page_li = page.find("li", {"class": "item pages-item-next"})
        if next_page_li and next_page_li.find("a"):
            return next_page_li.find("a")["href"]  # Return the next page URL
    return None  # No more pages

def scrape_rimi():
    shop_urls = [
        "https://www.rimi.lv/e-veikals/lv/produkti/alkoholiskie-dzerieni/c/SH-1?currentPage=1&pageSize=80",
        "https://www.rimi.lv/e-veikals/lv/produkti/dzerieni/c/SH-5?currentPage=1&pageSize=80",
    ]

    for url in shop_urls:
        current_page = url  # Start with the first page for the current category

        while current_page:  # Loop until there are no more pages
            print(f"Scraping: {current_page}")  # Show current page being scraped

            soup = fetch_page_content(current_page)  # Fetch the page

            products = soup.find_all("li", class_="product-grid__item")  # Extract products

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
                    tilpums_match = re.search(r"(\d+(?:\,\d*)?)\s*(ml|l)", tilpums_raw.string.replace(" ", ""))
                    if tilpums_match:
                        quantity = Decimal(tilpums_match.group(1).replace(",", "."))
                        unit = tilpums_match.group(2).lower()

                        volume = tilpums_match.group(0)
                        title = title.replace(volume, "").strip()

                        percent_match = re.search(r"(\d+(?:.\d*)?)\s*%", title, re.IGNORECASE)
                        if percent_match:
                            percentage = percent_match.group(0)  # The matched percentage (e.g., "13%")
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

                c.execute("""
                    INSERT INTO Kabinets (Name, Volume, Price, Store, Category, PricePerLiter, links)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                """, (title, float(tilpums) if tilpums else None, float(cena) if cena else None, "Rimi", None, float(cenaL) if cenaL else None, link))
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
    shop_urls = [
            "https://www.spiritsandwine.lv/lv/absints?page=1",
            "https://www.spiritsandwine.lv/lv/alus-alk?page=1",
            "https://www.spiritsandwine.lv/lv/alus-bezalk?page=1",
            "https://www.spiritsandwine.lv/lv/armanjaks?page=1",
            "https://www.spiritsandwine.lv/lv/bag-in-box-baltivins?page=1",
            "https://www.spiritsandwine.lv/lv/bag-in-box-sarkanvins?page=1",
            "https://www.spiritsandwine.lv/lv/bag-in-box-sartvins?page=1",
            "https://www.spiritsandwine.lv/lv/baltvins?page=1",
            "https://www.spiritsandwine.lv/lv/bezalkaholiskais-vins?page=1",
            "https://www.spiritsandwine.lv/lv/bezalkoholiskais-dzirkstosais-vins?page=1",
            "https://www.spiritsandwine.lv/lv/bezalkoholiskais-sidrs?page=1",
            "https://www.spiritsandwine.lv/lv/bezalkoholiskie-dzerieni?page=1",
            "https://www.spiritsandwine.lv/lv/bezalkoholiskie-kokteili?page=1",
            "https://www.spiritsandwine.lv/lv/bezalkoholiskie-stiprie-dzerieni?page=1",
            "https://www.spiritsandwine.lv/lv/brendijs?page=1",
            "https://www.spiritsandwine.lv/lv/degvins?page=1",
            "https://www.spiritsandwine.lv/lv/dzins?page=1",
            "https://www.spiritsandwine.lv/lv/dzirkstosais-vins?page=1",
            "https://www.spiritsandwine.lv/lv/energijas-dzerieni?page=1",
            "https://www.spiritsandwine.lv/lv/kalvadoss?page=1",
            "https://www.spiritsandwine.lv/lv/karstvini-karstie-dzerieni?page=1",
            "https://www.spiritsandwine.lv/lv/kokteili?page=1",
            "https://www.spiritsandwine.lv/lv/konjaks?page=1",
            "https://www.spiritsandwine.lv/lv/likieris?page=1",
            "https://www.spiritsandwine.lv/lv/mini?page=1",
            "https://www.spiritsandwine.lv/lv/rums?page=1",
            "https://www.spiritsandwine.lv/lv/vermuts?page=1",
            "https://www.spiritsandwine.lv/lv/sampanietis?page=1",
            "https://www.spiritsandwine.lv/lv/sarkanvins?page=1",
            "https://www.spiritsandwine.lv/lv/sartvins?page=1",
            "https://www.spiritsandwine.lv/lv/sidrs?page=1",
            "https://www.spiritsandwine.lv/lv/sirupi?page=1",
            "https://www.spiritsandwine.lv/lv/stiprinats-vins?page=1",
            "https://www.spiritsandwine.lv/lv/sula?page=1",
            "https://www.spiritsandwine.lv/lv/tekila?page=1",
            "https://www.spiritsandwine.lv/lv/udens?page=1",
            "https://www.spiritsandwine.lv/lv/uzlejums?page=1",
            "https://www.spiritsandwine.lv/lv/viskijs?page=1",
    ]

    for url in shop_urls:
        current_page = url  # Start with the first page for the current category

        while current_page:  # Loop until there are no more pages
            print(f"Scraping: {current_page}")  # Show current page being scraped

            soup = fetch_page_content(current_page)  # Fetch the page
            products = soup.find_all("div", class_="col mb-3")  # Extract products

            for product in products:
                # Extract product name
                title = product.find("h2", class_="product-title").string.strip()

                # Extract and clean price
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

                # Calculate price per liter if volume and price are valid
                try:
                    cenaL = float((cena / tilpums).quantize(Decimal("0.01"), rounding=ROUND_HALF_UP))
                except TypeError:
                    cena = None

                link = product.find("a", class_="text-decoration-none text-dark d-block mb-auto")["href"]
                link = "https://www.spiritsandwine.lv"+str(link)

                c.execute("""
                    INSERT INTO Kabinets (Name, Volume, Price, Store, Category, PricePerLiter, links)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                """, (title, float(tilpums) if tilpums else None, float(cena) if cena else None, "Spirits & Wine", None, float(cenaL) if cenaL else None, link))

            conn.commit()

            # Get the next page URL
            next_page = get_next_page_SandW(soup)
            current_page = next_page  # Update for the next loop iteration

def get_next_page_SandW(soup):
    # Find the div containing pagination
    pagination_div = soup.find("div", class_="pagination")
    if pagination_div:
        # Look for the "next page" button
        next_page_a = pagination_div.find("a", class_="btn-next")
        if next_page_a and "href" in next_page_a.attrs:
            # Return the URL for the next page
            return "https://www.spiritsandwine.lv"+str(next_page_a["href"])
    # If no next page is found, return None
    return None

def scrape_LB():
    shop_urls = [
            "https://www.lbveikali.lv/lv/stiprie-dzerieni?product_list_limit=96",
            "https://www.lbveikali.lv/lv/vins-un-dzirkstosie?product_list_limit=96",
            "https://www.lbveikali.lv/lv/alus-sidrs-un-kokteili?product_list_limit=96",
            "https://www.lbveikali.lv/lv/bezalkoholiskie-dzerieni-limonades?product_list_limit=96",
    ]

    for url in shop_urls:
        current_page = url  # Start with the first page for the current category

        while current_page:  # Loop until there are no more pages
            print(f"Scraping: {current_page}")  # Show current page being scraped

            soup = fetch_page_content(current_page)  # Fetch the page
            products = soup.find_all("li", class_="item product product-item")  # Extract products

            for product in products:
                # Extract product name
                title = product.find("a", class_="product-item-link").string.strip()

                # Extract and clean price
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

                link = product.find("a", class_="product-item-link")["href"]
                link = str(link)

                c.execute("""
                    INSERT INTO Kabinets (Name, Volume, Price, Store, Category, PricePerLiter, links)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                """, (title, float(tilpums) if tilpums else None, float(cena) if cena else None, "Latvijas Balzāms", None, float(cenaL) if cenaL else None, link))

            conn.commit()

            # Get the next page URL
            next_page = get_next_page_LB(soup)
            current_page = next_page  # Update for the next loop iteration

def get_next_page_LB(soup):
    # Find the div containing pagination
    page = soup.find("ul", class_="items pages-items")
    if page:
        # Look for the "next page" button
        next_page_li = page.find("li", {"class": "item pages-item-next"})
        if next_page_li and next_page_li.find("a"):
            return next_page_li.find("a")["href"]
    # If no next page is found, return None
    return None

if __name__ == "__main__":
    conn = sqlite3.connect("/var/www/mysite/database/kabinets.db")
    c = conn.cursor()

    c.execute("DELETE FROM Kabinets")
    conn.commit()

    scrape_alkoutlet()
    scrape_rimi()
    scrape_SandW()
    scrape_LB()

    c.execute(f"SELECT COUNT(*) FROM kabinets")
    row_count = c.fetchone()[0]

        # Print the row count
    print(f"Scraped {row_count} products")

    conn.close()
