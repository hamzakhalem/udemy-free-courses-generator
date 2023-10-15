from selenium import webdriver
from selenium.webdriver.firefox.options import Options
from selenium.common.exceptions import NoSuchElementException, StaleElementReferenceException
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException

link = ""

def logo():
    print("   _____                                ______   _       _               ")
    print("  / ____|                              |  ____| | |     | |              ")
    print(" | |     ___  _   _ _ __   ___  _ __   | |__ ___| |_ ___| |__   ___ _ __ ")
    print(" | |    / _ \| | | | '_ \ / _ \| '_ \  |  __/ _ \ __/ __| '_ \ / _ \ '__|")
    print(" | |___| (_) | |_| | |_) | (_) | | | | | | |  __/ || (__| | | |  __/ |   ")
    print("  \_____\___/ \__,_| .__/ \___/|_| |_| |_|  \___|\__\___|_| |_|\___|_|   ")
    print("                   | |                                                   ")
    print("                   |_|     			       V1.1   ")
    print("-------------------------------------------------------------------------")
    print("\t\t\t\t ABOUT")
    print("-------------------------------------------------------------------------")
    print("\t\t Author: Ahlyab")
    print("\t\t Github: https://www.github.com/Ahlyab")
    print("\t\t Note : Author is not responsible for any misuse!\n\t\t        This tool is completely legal.")
    print("-------------------------------------------------------------------------")

def get_link(url):
    driver.get(url)
    coupons = driver.find_elements_by_class_name("card-header")
    return coupons

def get_coupon_link_from_list(list,n):
    while n <= int(number):
        list[n].click()
        try:
    # Wait for the element to be present (adjust the timeout as needed)
            elemnt = WebDriverWait(driver, 5).until(
                EC.presence_of_element_located((By.LINK_TEXT, "Take Course"))
            )
            
            # Once the element is found, print it
            print(elemnt)

        except NoSuchElementException:
            # Handle the case where the element is not found
            print("Element not found")
            return ''
        url = elemnt.get_attribute("href")
        driver.get(url)
        link = driver.find_element_by_partial_link_text("https://www.udemy.com/").get_attribute("href")
        print(link)
        
        return link

def get_coupon_link_from_list_skip_add(list,n):
    while n <= int(number):
        driver.refresh()
        try:
    # Wait for the element to be present (adjust the timeout as needed)
            elemnt = WebDriverWait(driver, 5).until(
                EC.presence_of_element_located((By.LINK_TEXT, "Take Course"))
            )
            
            # Once the element is found, print it
            print(elemnt)

        except Exception:
            # Handle the case where the element is not found
            print("Element not found")
            return ''
        url = elemnt.get_attribute("href")
        driver.get(url)
        link = driver.find_element_by_partial_link_text("https://www.udemy.com/").get_attribute("href")
        # link = driver.find_element_by_partial_link_text("https://www.udemy.com/").get_attribute("href")
        return link
from urllib.parse import urlparse, urlunparse
def get_course_title(link):
    print("49 "+link)

    parsed_url = urlparse(link)
    # Remove the query parameters
    clean_url = urlunparse(parsed_url._replace(query=''))
    print(clean_url)
    driver.get(clean_url)
    # first_h1 = driver.find_element_by_tag_name("h1")
   # You can adjust the timeout
    try:
        element = WebDriverWait(driver, 5).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, 'h1[data-purpose="lead-title"]'))
        )
        print("Element exists on the page.")
    except TimeoutException:
        title = clean_url.replace("https://www.udemy.com/course/", "")
        title = title.replace('-',' ')
        return title
        print("Element was not found within the specified timeout.")
    # first_h1 = driver.find_element_by_css_selector('h1[data-purpose="lead-title"]')
    title = "------\n"+ element.text 
    return title
    # return ""
n = 0

logo()
print("[!] Only 30 coupons can be fetched so kindly do not input a value greater than 30")
number = int(input(">>> How many coupons you need: "))
if number >= 50:
    print("[-] Invalid Input! Please try again")
number = number - 1
file = open("free_coupons.txt", "w+",  encoding="utf-8")
options = Options()
options.add_argument('--headless')
driver = webdriver.Firefox(options=options)
print("[+] Please wait for several minutes......")
print("[+] Work in Progress!")
if number <= 14:
    while n <= number:
        coupon_list = get_link("https://www.discudemy.com/all")
        try:
            link_of_coupon = get_coupon_link_from_list(coupon_list, n)
        except NoSuchElementException or StaleElementReferenceException :
            link_of_coupon = get_coupon_link_from_list_skip_add(coupon_list, n)
        course_title = get_course_title(link_of_coupon)
        print(course_title)
        file.write(course_title + "--\n")
        file.write(link_of_coupon + "\n\n")

        n = n + 1
if number > 14 and number <= 28:
    while n <= number:
        coupon_list = get_link("https://www.discudemy.com/all")
        try:
            link_of_coupon = get_coupon_link_from_list(coupon_list, n)
        except NoSuchElementException or StaleElementReferenceException :
            link_of_coupon = get_coupon_link_from_list_skip_add(coupon_list, n)
        course_title = get_course_title(link_of_coupon)
        print(course_title)
        file.write("-----\n" +course_title + "\n")
        file.write(link_of_coupon + "\n\n")
        n = n + 1
        if n == 14:
            file.close()
            n = n - 14
            number = number - 14
            while n <= number:
                coupon_list = get_link("https://www.discudemy.com/all/2")
                try:
                    link_of_coupon = get_coupon_link_from_list(coupon_list, n)
                except NoSuchElementException or StaleElementReferenceException :
                    link_of_coupon = get_coupon_link_from_list_skip_add(coupon_list, n)
                new_modifications = open("free_coupons.txt", "a+" , encoding="utf-8")
                course_title = get_course_title(link_of_coupon)
                print(course_title)
                new_modifications.write("-----\n" +course_title + "\n")
                new_modifications.write(link_of_coupon + "\n\n")
                n = n + 1
if number > 28 :
    while n <= number:
        coupon_list = get_link("https://www.discudemy.com/all")
        try:
            link_of_coupon = get_coupon_link_from_list(coupon_list, n)
        except NoSuchElementException or StaleElementReferenceException :
            link_of_coupon = get_coupon_link_from_list_skip_add(coupon_list, n)
        course_title = get_course_title(link_of_coupon)
        print(course_title)
        file.write(course_title + "----\n")
        file.write(link_of_coupon + "\n\n")
        n = n + 1
        if n == 14:
            file.close()
            n = n - 14
            number = number - 14
            while n <= number:
                coupon_list = get_link("https://www.discudemy.com/all/2")
                try:
                    link_of_coupon = get_coupon_link_from_list(coupon_list, n)
                    course_title = get_course_title(link_of_coupon)
                    print(course_title)

                except NoSuchElementException or StaleElementReferenceException :
                    link_of_coupon = get_coupon_link_from_list_skip_add(coupon_list, n)
                new_modifications = open("free_coupons.txt", "a+" , encoding="utf-8")
                new_modifications.write(course_title + "----\n")
                new_modifications.write(link_of_coupon + "\n\n")
                n = n + 1
                if n == 14:
                    new_modifications.close()
                    n = n - 14
                    number = number - 14
                    while n <= number:
                        coupon_list = get_link("https://www.discudemy.com/all/3")
                        course_title = get_course_title(link_of_coupon)
                        try:
                            link_of_coupon = get_coupon_link_from_list(coupon_list, n)
                        except NoSuchElementException or StaleElementReferenceException :
                            link_of_coupon = get_coupon_link_from_list_skip_add(coupon_list, n)
                        new_modifications_2 = open("free_coupons.txt", "a+" , encoding="utf-8")
                        new_modifications_2.write(course_title + "----\n")
                        new_modifications_2.write(link_of_coupon + "\n\n")

                        n = n + 1

file.close()
driver.close()
print("[+] All coupons are saved in free_coupons.txt file")
print("[-] Quiting Program!")

