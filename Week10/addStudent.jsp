<%@ page import="java.sql.*" %>
<%@ page import="com.techhive.DBConnection" %>
<%@ page language="java" contentType="text/html; charset=UTF-8" %>
<%
    HttpSession s = request.getSession(false);
    if (s == null || s.getAttribute("user") == null) {
        response.sendRedirect("login.jsp");
        return;
    }
%>
<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <style>
        body { font-family: Arial; background: #f1f5f9;
               display: flex; justify-content: center;
               align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 32px;
                border-radius: 8px; width: 350px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #0f172a; margin-bottom: 20px; }
        input { width: 100%; padding: 10px; margin-bottom: 14px;
                border: 1px solid #cbd5e1; border-radius: 6px;
                box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #f97316;
                 color: white; border: none; border-radius: 6px;
                 cursor: pointer; }
        .success { color: green; font-size: 13px; 
                   margin-bottom: 10px; }
        .error { color: red; font-size: 13px; 
                 margin-bottom: 10px; }
        a { display: block; text-align: center; 
            margin-top: 14px; color: #f97316; 
            text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Add New Student</h2>

        <% String msg = (String) request.getAttribute("msg");
           String err = (String) request.getAttribute("err");
           if (msg != null) { %>
            <p class="success"><%= msg %></p>
        <% } %>
        <% if (err != null) { %>
            <p class="error"><%= err %></p>
        <% } %>

        <form method="POST" action="StudentServlet?action=add">
            <input type="text" name="name" 
                   placeholder="Full Name" required>
            <input type="text" name="course" 
                   placeholder="Course" required>
            <input type="email" name="email" 
                   placeholder="Email" required>
            <button type="submit">Add Student</button>
        </form>
        <a href="viewStudents.jsp">View All Students</a>
        <a href="welcome.jsp">Back to Dashboard</a>
    </div>
</body>
</html>