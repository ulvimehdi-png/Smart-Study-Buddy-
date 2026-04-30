import pymysql
from pymysql.cursors import DictCursor


def get_connection():
    return pymysql.connect(
        host="mysql.studev.groept.be",
        port=3306,
        user="a25sewt301",
        password="EwSlrkVQ",
        database="a25sewt301",
        cursorclass=DictCursor,
        autocommit=False,
        charset="utf8mb4",
    )