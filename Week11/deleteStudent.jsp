<%@ page language="java" contentType="text/html; charset=UTF-8" %>
<%@ page import="java.sql.*" %>
<%@ page import="com.techhive.DBConnection" %>
<%
    HttpSession s = request.getSession(false);
    if (s == null || s.getAttribute("user") == null) {
        response.sendRedirect("login.jsp");
        return;
    }
    String id = request.getParameter("id");
    Connection conn = null;
    try {
        conn = DBConnection.getConnection();
        PreparedStatement ps = conn.prepareStatement(
            "DELETE FROM students WHERE id=?");
        ps.setInt(1, Integer.parseInt(id));
        ps.executeUpdate();
        ps.close();
    } catch (Exception e) {
        e.printStackTrace();
    } finally {
        if (conn != null) conn.close();
    }
    response.sendRedirect(
        "viewStudents.jsp?msg=Student+deleted+successfully");
%>