import pymysql
import re

# Read .env file
env_vars = {}
with open('../php-backend/.env', 'r', encoding='utf-8') as f:
    for line in f:
        line = line.strip()
        if not line or line.startswith('#'):
            continue
        if '=' in line:
            k, v = line.split('=', 1)
            env_vars[k.strip()] = v.strip()

host = env_vars.get('DB_HOST', 'localhost')
db_name = env_vars.get('DB_NAME', 'deckklip_atelier_noir')
user = env_vars.get('DB_USER', 'deckklip_deckklipscom_denemek1')
password = env_vars.get('DB_PASS', '')

print(f"Connecting to host={host}, db={db_name}, user={user}")

try:
    connection = pymysql.connect(
        host=host,
        user=user,
        password=password,
        database=db_name,
        cursorclass=pymysql.cursors.DictCursor
    )
    with connection.cursor() as cursor:
        cursor.execute("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'ai_%'")
        rows = cursor.fetchall()
        print("\nAI Settings in Database:")
        for r in rows:
            # Mask API keys
            val = r['setting_value']
            if 'key' in r['setting_key'] and val:
                val = val[:6] + "..." + val[-4:] if len(val) > 10 else "***"
            print(f"  {r['setting_key']}: {val}")
finally:
    if 'connection' in locals() and connection.open:
        connection.close()
