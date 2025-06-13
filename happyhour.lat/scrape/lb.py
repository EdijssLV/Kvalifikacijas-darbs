from . import stores
from .utils import fetch_page_content
from decimal import Decimal, ROUND_HALF_UP
import re
from . import db
from .pagination import get_next_page_LB
import sqlite3

def scrape_LB():
    for url in stores.LB_urls:
        current_page = url

        while current_page:
            print(f"Scraping: {current_page}")

            soup = fetch_page_content(current_page)
            if soup is None:
                print(f"Skipping page due to fetch failure: {current_page}")
                break
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

                db.insertDatabase(title, tilpums, cena, "Latvijas Balzāms" ,kategorija, cenaL, link)

            next_page = get_next_page_LB(soup)
            current_page = next_page