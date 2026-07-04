<%@ page language="java" contentType="text/html; charset=UTF-8" %>
<%
    String savedUsername = "";
    boolean isRemembered = false;
    Cookie[] cookies = request.getCookies();
    if (cookies != null) {
        for (Cookie c : cookies) {
            if (c.getName().equals("rememberedUser") 
                && !c.getValue().isEmpty()) {
                savedUsername = c.getValue();
                isRemembered = true;
            }
        }
    }
%>
<!DOCTYPE html>
<html>
<head>
    <title>Student Login</title>
    <style>
        body { font-family: Arial; background: #f1f5f9; 
               display: flex; justify-content: center; 
               align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 32px; 
                border-radius: 8px; width: 300px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 20px; color: #0f172a; }
        input[type=text], input[type=password] { 
                width: 100%; padding: 10px; margin-bottom: 14px;
                border: 1px solid #cbd5e1; border-radius: 6px;
                box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #f97316;
                 color: white; border: none; border-radius: 6px;
                 cursor: pointer; font-size: 14px; }
        .error { color: red; font-size: 13px; margin-bottom: 10px; }
        .remember { display: flex; align-items: center; 
                    gap: 8px; margin-bottom: 14px; 
                    font-size: 13px; color: #475569; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Student Login</h2>

        <% String error = (String) request.getAttribute("error");
           if (error != null) { %>
            <p class="error"><%= error %></p>
        <% } %>

        <% if (isRemembered) { %>
            <p style="color:green; font-size:13px; 
                      margin-bottom:10px;">
                Welcome back, <%= savedUsername %>!
            </p>
        <% } %>

        <form method="POST" action="LoginServlet">
            <input type="text" 
                   name="username"
                   placeholder="Username"
                   value="<%= savedUsername %>"
                   required>
            <input type="password" 
                   name="password"
                   placeholder="Password" 
                   required>
            <div class="remember">
                <input type="checkbox" 
                       name="rememberMe"
                       id="rememberMe"
                       style="width:auto; margin:0"
                       <%= isRemembered ? "checked" : "" %>>
                <label for="rememberMe">Remember Me</label>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>