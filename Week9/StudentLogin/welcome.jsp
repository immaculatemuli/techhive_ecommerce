<%
    // Add theme preference cookie
    Cookie themeCookie = new Cookie("theme", "dark");
    themeCookie.setMaxAge(60 * 60 * 24 * 30);
    response.addCookie(themeCookie);

    // Protect page
    HttpSession s = request.getSession(false);
    if (s == null || s.getAttribute("user") == null) {
        response.sendRedirect("login.jsp");
        return;
    }
    String username = (String) s.getAttribute("user");
    String loginTime = (String) s.getAttribute("loginTime");
%>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
    <style>
        body { font-family: Arial; background: #0f172a;
               color: white; display: flex;
               justify-content: center; align-items: center;
               height: 100vh; margin: 0; }
        .card { background: #1e293b; padding: 32px;
                border-radius: 8px; width: 380px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.3); }
        h2 { color: #f97316; }
        p { color: #94a3b8; font-size: 14px; }
        .info { background: #0f172a; padding: 12px;
                border-radius: 6px; margin: 16px 0; }
        .info p { margin: 6px 0; }
        .badge { display: inline-block; background: #f97316;
                 color: white; font-size: 11px; padding: 2px 8px;
                 border-radius: 20px; margin-left: 6px; }
        a { display: block; text-align: center;
            background: #ef4444; color: white;
            padding: 10px; border-radius: 6px;
            text-decoration: none; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Welcome, <%= username %>!</h2>
        <p>You are now logged in to the Student Portal.</p>
        <div class="info">
            <p>Session Username: <strong><%= username %></strong></p>
            <p>Login Time: <strong><%= loginTime %></strong></p>
            <p>Session ID: <strong>
                <%= session.getId().substring(0,12) %>...
            </strong></p>
            <p>Theme Cookie: <strong>dark</strong>
                <span class="badge">saved in browser</span>
            </p>
        </div>
        <a href="logout.jsp">Logout</a>
    </div>
</body>
</html>