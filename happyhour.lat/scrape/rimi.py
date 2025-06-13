from . import stores
from .utils import fetch_page_content
from decimal import Decimal, ROUND_HALF_UP
import re
from . import db
from .pagination import get_next_page_rimi
import sqlite3

def scrape_rimi():
    for url in stores.rimi_urls:
        current_page = url

        while current_page:
            print(f"Scraping: {current_page}")

            soup = fetch_page_content(current_page)

            if soup is None:
                print(f"Skipping page due to failed fetch: {current_page}")
                break
            else:
                products = soup.find_all("li", class_="product-grid__item")
                for product in products:
                    title = product.find("p", class_="card__name").string.strip()

                    try:
                        price_div = product.find("div", class_="price-tag card__price")
                        whole_number = price_div.find("span").string.strip()
                        cents = price_div.find("sup").string.strip()
                        cena = Decimal(f"{whole_number}.{cents}")
                    except AttributeError:
                        cena = None

                    try:
                        link = product.find("a", class_="card__url js-gtm-eec-product-click")["href"]
                        link = "https://www.rimi.lv"+str(link)
                    except AttributeError:
                        link = None

                    try:
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
                    except AttributeError:
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

                    db.insertDatabase(title, tilpums, cena, "Rimi" ,kategorija, cenaL, link)

                next_page = get_next_page_rimi(soup)
                current_page = next_page