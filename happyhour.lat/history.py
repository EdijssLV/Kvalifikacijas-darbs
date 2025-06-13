import sqlite3
from datetime import datetime

def update_categories():
    conn = sqlite3.connect("/var/www/mysite/database/kabinets.db")
    cursor = conn.cursor()

    try:
        today_date = datetime.now().strftime("%Y-%m-%d")

        cursor.execute("""
            SELECT COUNT(*)
            FROM categories
            WHERE timestamp = ?
        """, (today_date,))
        if cursor.fetchone()[0] > 0:
            print("Data for today already exists in the categories table.")
            return

        cursor.execute("""
            SELECT category, ROUND(AVG(PricePerLiter), 2) AS avg_price
            FROM (
                SELECT category, PricePerLiter FROM Kabinets
                WHERE PricePerLiter IS NOT NULL
                UNION ALL
                SELECT category, PricePerLiter FROM nemainigs
                WHERE PricePerLiter IS NOT NULL
            ) AS Combined
            WHERE category IS NOT NULL
            GROUP BY category;
        """)
        rows = cursor.fetchall()
        for row in rows:
            category = row[0]
            avg_price = row[1]

            if category:
                cursor.execute("""
                    INSERT INTO categories (category, avg_price, timestamp)
                    VALUES (?, ?, ?)
                """, (category, avg_price, today_date))

        conn.commit()
        print("Categories table updated successfully.")

    except Exception as e:
        print(f"An error occurred: {e}")
        conn.rollback()

    finally:
        conn.close()

if __name__ == "__main__":
    update_categories()
