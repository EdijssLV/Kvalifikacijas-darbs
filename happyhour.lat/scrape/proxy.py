import requests
import random
from colorama import init, Fore, Style

cached_proxy = None

def get_working_proxy(api_url):
    global cached_proxy

    if cached_proxy:
        print(f"{Fore.GREEN}Using cached proxy: {cached_proxy}{Style.RESET_ALL}")
        return cached_proxy

    try:
        response = requests.get(api_url, timeout=10)
        proxy_list = response.text.strip().splitlines()
        random.shuffle(proxy_list)

        for proxy in proxy_list:
            proxy = proxy.strip()
            if not proxy:
                continue
            print(f"{Fore.YELLOW}Testing proxy: {proxy}{Style.RESET_ALL}")
            proxies = {"http": proxy, "https": proxy}
            try:
                r = requests.get("https://httpbin.org/ip", proxies=proxies, timeout=5)
                if r.status_code == 200:
                    print(f"{Fore.GREEN}Proxy working: {proxy}{Style.RESET_ALL}")
                    cached_proxy = proxy
                    return proxy
            except Exception as e:
                print(f"{Fore.RED}Proxy failed: {proxy} | Reason: {e}{Style.RESET_ALL}")
                continue

    except Exception as e:
        print(f"{Fore.RED}Failed to fetch proxies: {e}{Style.RESET_ALL}")
    return None

def reset_cached_proxy():
    global cached_proxy
    cached_proxy = None