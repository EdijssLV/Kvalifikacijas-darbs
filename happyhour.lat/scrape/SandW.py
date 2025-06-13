from . import stores
from .utils import fetch_page_content
from decimal import Decimal, ROUND_HALF_UP
import re
from . import db
from .pagination import get_next_page_SandW
import sqlite3

def scrape_SandW():
    for url in stores.SandWine_urls:
        current_page = url
        while current_page:
            print(f"Current page: {current_page}")
            soup = fetch_page_content(current_page)

            if soup is None:
                print(f"Skipping page due to failed fetch: {current_page}")
                break
            else:
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
                        cenaL = None

                    link = product.find("a", class_="text-decoration-none text-dark d-block mb-auto")["href"]
                    link = "https://www.spiritsandwine.lv"+str(link)

                    db.insertDatabase(title, tilpums, cena, "Spirits & Wine" ,kategorija, cenaL, link)


                next_page = get_next_page_SandW(soup)
                current_page = next_page