<%@ page language="java" contentType="text/html; charset=UTF-8" %>
<%
    HttpSession s = request.getSession(false);
    if (s != null && s.getAttribute("user") != null) {
        response.sendRedirect("welcome.jsp");
        return;
    }
    String savedUsername = "";
    Cookie[] cookies = request.getCookies();
    if (cookies != null) {
        for (Cookie c : cookies) {
            if (c.getName().equals("rememberedUser"))
                savedUsername = c.getValue();
        }
    }
    String error = (String) request.getAttribute("error");
%>
<!DOCTYPE html>
<html>
<head>
    <title>Student Portal — Sign In</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',Arial,sans-serif;
             background:#f7f8fa;min-height:100vh;
             display:flex;align-items:center;
             justify-content:center;padding:24px}
        .card{background:#fff;border:1px solid #eaecf0;
              border-radius:14px;padding:40px;
              width:100%;max-width:380px;
              box-shadow:0 4px 24px rgba(0,0,0,.06)}
        .logo{font-size:18px;font-weight:600;
              color:#1a1a2e;margin-bottom:4px}
        .logo b{color:#1a7a4a}
        .sub{font-size:14px;color:#9ca3af;margin-bottom:32px}
        .fg{display:flex;flex-direction:column;
            gap:6px;margin-bottom:18px}
        .fg label{font-size:11px;font-weight:600;color:#9ca3af;
                  text-transform:uppercase;letter-spacing:.5px}
        .fg input{padding:12px 14px;border:1px solid #e5e7eb;
                  border-radius:8px;font-size:14px;color:#1a1a2e;
                  background:transparent;outline:none;
                  transition:border .15s}
        .fg input:focus{border-color:#1a7a4a;
                        box-shadow:0 0 0 3px rgba(26,122,74,.08)}
        .fg input::placeholder{color:#d1d5db}
        .remember{display:flex;align-items:center;gap:8px;
                  margin-bottom:24px;font-size:13px;color:#6b7280}
        .remember input{width:14px;height:14px;
                         accent-color:#1a7a4a;cursor:pointer}
        .btn{width:100%;background:#1a7a4a;color:#fff;
             border:none;padding:12px;border-radius:8px;
             font-size:14px;font-weight:500;cursor:pointer;
             transition:background .15s}
        .btn:hover{background:#145e38}
        .err{background:#fee2e2;border:1px solid #fca5a5;
             color:#dc2626;padding:10px 14px;border-radius:8px;
             margin-bottom:18px;font-size:13px}
        .footer{text-align:center;margin-top:20px;
                font-size:13px;color:#9ca3af}
        .footer a{color:#1a7a4a;text-decoration:none;font-weight:500}
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">Student<b>Portal</b></div>
        <div class="sub">Sign in to your account to continue</div>

        <% if (error != null) { %>
            <div class="err"><%= error %></div>
        <% } %>

        <form method="POST" action="LoginServlet">
            <div class="fg">
                <label>Username</label>
                <input type="text" name="username"
                       value="<%= savedUsername %>"
                       placeholder="Enter your username" required>
            </div>
            <div class="fg">
                <label>Password</label>
                <input type="password" name="password"
                       placeholder="Enter your password" required>
            </div>
            <div class="remember">
                <input type="checkbox" name="rememberMe"
                       id="rm"
                       <%= !savedUsername.isEmpty() ? "checked" : "" %>>
                <label for="rm">Remember me</label>
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>
        <div class="footer">
            Logged in as admin?
            <a href="welcome.jsp">Go to Dashboard</a>
        </div>
    </div>
</body>
</html>