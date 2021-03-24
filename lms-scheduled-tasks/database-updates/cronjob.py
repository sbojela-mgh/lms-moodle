from datetime import date
from datetime import datetime
import time
import mysql.connector



config = { #NEED TO CHANGE THESE VALUES TO REFLECT THE CORRECT VALUES IN TEST AND PROD
  'user': 'root',
  'password': 'root',
  'unix_socket': '/Applications/MAMP/tmp/mysql/mysql.sock',
  'database': 'moodledb',
  'raise_on_warnings': True,
}

link = mysql.connector.connect(**config)



cursor = link.cursor()
curTime = str(int(time.time()))

categoryID = "34" # <---NEED TO CHANGE THIS TO THE "PAST OFFERINGS" CATEGORY ID ON OUR TEST / PROD DB

cursor.execute("SELECT c.category, c.id from mdl_course c WHERE category <> 0 AND category <> " + categoryID + " AND c.enddate <=" + curTime + ";")
to_update = []

for row in cursor:
    print(row[0])
    to_update.append(row)
print(to_update)
for i in to_update:

    cursor.execute("UPDATE mdl_course SET category = " + categoryID + " WHERE id = "+ str(i[1]) + ";")
    link.commit()
cursor.execute("UPDATE mdl_course_categories SET coursecount = coursecount + "  + str(len(to_update)) + " WHERE id = " + categoryID + ";")
for i in to_update:
    cursor.execute("UPDATE mdl_course_categories SET coursecount = coursecount - 1 WHERE id = "+ str(i[0]) + ";")
    link.commit()