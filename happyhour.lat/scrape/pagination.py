def get_next_page_alkoutlet(soup):
    page = soup.find("ul", {"class": "items pages-items"})
    if page:
        next_page_li = page.find("li", {"class": "item pages-item-next"})
        if next_page_li and next_page_li.find("a"):
            return next_page_li.find("a")["href"]
    return None

def get_next_page_SandW(soup):
    pagination_div = soup.find("div", class_="pagination")
    if pagination_div:
        next_page_a = pagination_div.find("a", class_="btn-next")
        if next_page_a and "href" in next_page_a.attrs:
            return "https://www.spiritsandwine.lv"+str(next_page_a["href"])
    return None

def get_next_page_rimi(soup):
    page = soup.find("ul", class_="pagination__list")
    if page:
        next_page_li = page.find_all("li", class_="pagination__item -chevron")
        next_page_li = next_page_li[-1]
        if next_page_li and next_page_li.find("a"):
            return "https://www.rimi.lv"+str(next_page_li.find("a")["href"])

def get_next_page_lats(soup):
    page = soup.find("div", class_="product_listing_main_switcher_page_line")
    if page:
        active = page.find("a", class_="active")
        if active:
            next_sibling = active.find_next_sibling("a")
            if next_sibling and "href" in next_sibling.attrs:
                return "https://www.lats.lv" + str(next_sibling["href"])
    return None

def get_next_page_LB(soup):
    page = soup.find("ul", class_="items pages-items")
    if page:
        next_page_li = page.find("li", {"class": "item pages-item-next"})
        if next_page_li and next_page_li.find("a"):
            return next_page_li.find("a")["href"]
    return None