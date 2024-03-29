#!/usr/bin/python3
# -*- coding: UTF-8 -*-

import cgitb
import sys

cgitb.enable()

print ("Content-Type: text/plain;charset=utf-8")
print ()

print ("Hello World!")
print (sys.version)

i = 1
while i <= 10:
    print(i ** 2)
    i += 1

