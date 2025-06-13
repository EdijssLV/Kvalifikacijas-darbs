from . import stores
from .utils import fetch_page_content
from decimal import Decimal, ROUND_HALF_UP
import re
from . import db
from .pagination import get_next_page_lats

def scrape_lats():
    for url in stores.lats_urls:
        current_page = url
        while current_page:
            print(f"Current page: {current_page}")
            soup = fetch_page_content(current_page)
            if soup is None:
                print(f"Skipping page due to fetch failure: {current_page}")
                break
            else:
                products = soup.find_all("div", class_="-oProduct")

                for product in products:
                    title = product.find("a", class_="-oTitle").string.strip()

                    price_div = product.find("div", class_="-oPrice")
                    try:
                        cena = price_div.find("div").string.strip()
                        cena = Decimal(cena.replace("€ ", "").replace(",", ".").strip()) if price_div else None
                    except AttributeError:
                        cena = None
                    
                    volume = product.find("a", class_="-oTitle").string.strip()
                    if volume:
                        tilpums_match = re.search(r"(\d+(?:[.,]\d*)?)\s*(ml|l)", volume, re.IGNORECASE)

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
                    
                    link = product.find("a", class_="-oTitle")["href"]
                    link = "https://www.e-latts.lv"+str(link)

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

                    db.insertDatabase(title, tilpums, cena, "LaTS" ,None , cenaL, link)
                
                next_page = get_next_page_lats(soup)
                current_page = next_page