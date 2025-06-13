from . import stores
from .utils import fetch_page_content
from decimal import Decimal, ROUND_HALF_UP
import re
from . import db
from .pagination import get_next_page_alkoutlet
import sqlite3

def scrape_alkoutlet():
    for url in stores.AlkOutlet_urls:
        current_page = url
        while current_page:
            print(f"Current page: {current_page}")
            soup = fetch_page_content(current_page)
            if soup is None:
                print(f"Skipping page due to fetch failure: {current_page}")
                break
            else:
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


                    db.insertDatabase(title, tilpums, cena, "AlkOutlet" ,kategorija, cenaL, link)
                

                next_page = get_next_page_alkoutlet(soup)
                current_page = next_page