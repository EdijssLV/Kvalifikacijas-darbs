import sqlite3
from datetime import datetime

def process_data_and_insert():
    conn = sqlite3.connect('/var/www/mysite/databse/kabinets.db')
    cursor = conn.cursor()
    Store_query = """
    SELECT Store, AVG(PricePerLiter) AS avg_price
    FROM (
        SELECT Store, PricePerLiter FROM Kabinets
        UNION ALL
        SELECT Store, PricePerLiter FROM nemainigs
    )
    GROUP BY Store
    """

    category_query = """
    SELECT category, AVG(PricePerLiter) AS avg_price
    FROM (
        SELECT category, PricePerLiter FROM Kabinets
        UNION ALL
        SELECT category, PricePerLiter FROM nemainigs
    )
    GROUP BY category
    """

    cursor.execute(Store_query)
    Store_data = cursor.fetchall()
    
    cursor.execute(category_query)
    category_data = cursor.fetchall()
    
    timestamp = datetime.now().date()
    
    for row in Store_data:
        cursor.execute("INSERT INTO store (Store, avg_price, date) VALUES (?, ?, ?)", (row[0], row[1], timestamp))
    
    for row in category_data:
        cursor.execute("INSERT INTO category (category, avg_price, date) VALUES (?, ?, ?)", (row[0], row[1], timestamp))
    
    conn.commit()
    conn.close()

if __name__ == "__main__":
    process_data_and_insert()
