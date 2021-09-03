from datetime import date
from datetime import datetime
import time
import mysql.connector
import os.path
import pathlib

path = pathlib.Path().absolute()

f = str(path) + '/../../config.php'
print(f)

#fetching all the values needed to set up connection to db
with open(f, 'r') as config:
    for i in config.readlines():
        info = i.split()
        if 'dbhost' in i:
            host = info[-1][1:-2]
        if 'dbuser' in i:
            uname = info[-1][1:-2]
        if 'dbpass' in i:
            pword = info[-1][1:-2]
        if 'dbname' in i:
            dbname = info[-1][1:-2]

    #print(host)
    #print(uname)
    #print(pword)
    #print(dbname)

config = {
  'user': uname,
  'password': pword,
  'host': 'mysql4.research.partners.org';,#have to get rid of the ':3306' at the end of the string
  'database': dbname,
  'raise_on_warnings': True,
}

link = mysql.connector.connect(**config)
#setting up the connection...

cursor = link.cursor()
curTime = str(int(time.time()))
#grabbing today's timestamp to run query accordingly

cursor.execute("SELECT c.id, c.name from mdl_course_categories c WHERE c.name = 'Past Offerings';")
#grabbing the category ID of the "Past Offerings" category (we want to set the category of outdated courses to this category)


for row in cursor:
    categoryID = str(row[0])

cursor.execute("SELECT c.id, c.name from mdl_course_categories c WHERE c.name = 'On Demand';")
#grabbing the category ID of "On Demand" category (we don't want to update courses with this category ID)

for row in cursor:
    onDemandID = str(row[0])

cursor.execute("SELECT c.id, c.name from mdl_course_categories c WHERE c.name = 'Templates';")
#grabbing the category ID of "Templates" category (we don't want to update courses with this category ID)

for row in cursor:
    templatesID = str(row[0])

cursor.execute("SELECT c.id, c.name from mdl_course_categories c WHERE c.name = 'Pending';")
#grabbing the category ID of "Pending" category (we don't want to update courses with this category ID)

for row in cursor:
    pendingID = str(row[0])

cursor.execute("SELECT c.category, c.id, c.enddate from mdl_course c WHERE category <> 0 AND category <> " + onDemandID  + " AND category <> " + categoryID + " AND category <> " + templatesID + " AND category <> " + pendingID + " AND c.enddate <=" + curTime + ";")

to_update = []
#array where we add all the rows of courses that need to be updated


#some print staements to verify that we correctly fetched courses that already ended
for row in cursor:
    today = int(curTime)
    print("today:")
    print(datetime.utcfromtimestamp(today).strftime('%Y-%m-%d %H:%M:%S'))
    endDate = int(row[2])
    print("course ended on:")
    print(datetime.utcfromtimestamp(endDate).strftime('%Y-%m-%d %H:%M:%S'))
    to_update.append(row) #also appending them to an array that holds all the courses that need to be updated
    #array has the following format:
    #to_update = [[course category, course ID, course end date], [...], [...], ... , [...]]

#to_update[i] = [course category, course ID, course end date]



for i in to_update:
    #we use i[1] because that is where the course ID lies in our array of courses that need to be updated
    cursor.execute("UPDATE mdl_course SET category = " + categoryID + " WHERE id = "+ str(i[1]) + ";")
    link.commit()

cursor.execute("UPDATE mdl_course_categories SET coursecount = coursecount + "  + str(len(to_update)) + " WHERE id = " + categoryID + ";")

for i in to_update:
    cursor.execute("UPDATE mdl_course_categories SET coursecount = coursecount - 1 WHERE id = "+ str(i[0]) + ";")
    link.commit()

link.close()
