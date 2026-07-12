package com.techhive;

import java.sql.*;

public class DBConnection {
    
    private static final String URL = 
        "jdbc:mysql://localhost:3306/studentdb";
    private static final String USER = "root";
    private static final String PASSWORD = "";
    
    public static Connection getConnection() 
            throws SQLException {
        try {
            Class.forName("com.mysql.cj.jdbc.Driver");
            return DriverManager.getConnection(
                URL, USER, PASSWORD);
        } catch (ClassNotFoundException e) {
            throw new SQLException(
                "MySQL Driver not found: " + e.getMessage());
        }
    }
}