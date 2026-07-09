# Week 10 - Dynamic HTML Generation and JDBC Database Integration

**Environment:** Eclipse IDE + Apache Tomcat + Java + MySQL + JDBC

---

## Database Setup

CREATE DATABASE studentdb;

CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    course VARCHAR(100),
    email VARCHAR(100)
);

---

## Fig 1 - Database Creation in phpMyAdmin
![Fig 1](screenshots/fig1-database.png)

studentdb created in phpMyAdmin showing the students table.

---

## Fig 2 - Students Table with Sample Data
![Fig 2](screenshots/fig2-students-table.png)

students table showing all records with id, name, course
and email columns.

---

## Fig 3 - Add Student Form
![Fig 3](screenshots/fig3-add-form.png)

addStudent.jsp form for inserting new student records
into studentdb.

---

## Fig 4 - Student Added Successfully
![Fig 4](screenshots/fig4-success.png)

Success message after student inserted using INSERT INTO
via JDBC PreparedStatement.

---

## Fig 5 - View All Students Dynamically
![Fig 5](screenshots/fig5-view-students.png)

viewStudents.jsp fetching all records from studentdb
using SELECT query and displaying dynamically in JSP table.

---

## Fig 6 - DBConnection.java JDBC Code
![Fig 6](screenshots/fig6-dbconnection-code.png)

DBConnection.java showing JDBC connection using
DriverManager.getConnection() with studentdb connection string.

---

## JDBC vs PHP Comparison

| Task | Java JDBC | PHP PDO |
|---|---|---|
| Connect to DB | DriverManager.getConnection() | new PDO() |
| Execute Query | PreparedStatement | prepare() |
| Retrieve Records | ResultSet | fetchAll() |
| Prevent SQL Injection | PreparedStatement | Prepared Statement |

---

## Reflection
Week 10 introduced dynamic HTML generation and JDBC database
connectivity. Fig 1 shows studentdb created in phpMyAdmin with
the students table. Fig 2 shows the students table containing
sample records. Fig 3 shows the add student form. Fig 4 shows
the success message after a student is added using INSERT INTO.
Fig 5 shows all students fetched dynamically using SELECT and
displayed in a JSP table. Fig 6 shows DBConnection.java
establishing the JDBC connection using DriverManager.getConnection().
