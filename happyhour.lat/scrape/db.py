import sqlite3
from colorama import init, Fore, Style


def open_connection():
    global conn, c
    conn = sqlite3.connect("/var/www/mysite/database/kabinets.db")
    c = conn.cursor()
    print("Database connection opened.")

def close_connection():
    c.execute(f"SELECT COUNT(*) FROM kabinets")
    row_count = c.fetchone()[0]
    print(f"Scraped {row_count} products")

    c.close()
    conn.close()
    print("Database connection closed.")

def clearDatabase():
    c.execute("DELETE FROM Kabinets")
    conn.commit()
    print("Database cleared.")

def insertDatabase(title, tilpums, cena, store, kategorija, cenaL, link):
    try:
        c.execute("""
            INSERT INTO Kabinets (Name, Volume, Price, Store, Category, PricePerLiter, links)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        """, (
            str(title) if title else None,
            float(tilpums) if tilpums else None,
            float(cena) if cena else None,
            str(store) if store else None,
            str(kategorija) if kategorija else None,
            float(cenaL) if cenaL else None,
            str(link) if link else None
        ))
        conn.commit()
        print(f"{Fore.YELLOW}Inserted:{Style.RESET_ALL} {title} | {tilpums} | {cena} | {store} | {kategorija} | {cenaL} | {link}")
    except Exception as e:
        print("Error inserting into DB:", e)


def update_categories():
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
    print("Categories updated.")