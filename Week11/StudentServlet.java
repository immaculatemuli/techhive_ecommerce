package com.techhive;

import java.io.*;
import java.sql.*;
import jakarta.servlet.*;
import jakarta.servlet.http.*;
import jakarta.servlet.annotation.*;

@WebServlet("/StudentServlet")
public class StudentServlet extends HttpServlet {

    protected void doPost(HttpServletRequest request,
                          HttpServletResponse response)
            throws ServletException, IOException {

        String action = request.getParameter("action");

        if ("add".equals(action)) {
            String name   = request.getParameter("name");
            String course = request.getParameter("course");
            String email  = request.getParameter("email");
            Connection conn = null;
            try {
                conn = DBConnection.getConnection();
                PreparedStatement ps = conn.prepareStatement(
                    "INSERT INTO students(name,course,email)" +
                    " VALUES(?,?,?)");
                ps.setString(1, name);
                ps.setString(2, course);
                ps.setString(3, email);
                ps.executeUpdate();
                ps.close();
                response.sendRedirect(
                    "viewStudents.jsp?msg=" +
                    "Student+added+successfully");
            } catch (Exception e) {
                response.sendRedirect(
                    "viewStudents.jsp?msg=Add+failed:+" +
                    e.getMessage());
            } finally {
                try {
                    if (conn != null) conn.close();
                } catch (Exception e) {
                    e.printStackTrace();
                }
            }

        } else if ("edit".equals(action)) {
            String id     = request.getParameter("id");
            String name   = request.getParameter("name");
            String course = request.getParameter("course");
            String email  = request.getParameter("email");
            Connection conn = null;
            try {
                conn = DBConnection.getConnection();
                PreparedStatement ps = conn.prepareStatement(
                    "UPDATE students SET name=?," +
                    " course=?, email=? WHERE id=?");
                ps.setString(1, name);
                ps.setString(2, course);
                ps.setString(3, email);
                ps.setInt(4, Integer.parseInt(id.trim()));
                ps.executeUpdate();
                ps.close();
                response.sendRedirect(
                    "viewStudents.jsp?msg=" +
                    "Student+updated+successfully");
            } catch (Exception e) {
                response.sendRedirect(
                    "viewStudents.jsp?msg=Update+failed:+" +
                    e.getMessage());
            } finally {
                try {
                    if (conn != null) conn.close();
                } catch (Exception e) {
                    e.printStackTrace();
                }
            }
        }
    }
}