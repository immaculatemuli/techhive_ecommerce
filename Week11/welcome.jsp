<%@ page language="java" contentType="text/html; charset=UTF-8" %>
<%
    HttpSession s = request.getSession(false);
    if (s == null || s.getAttribute("user") == null) {
        response.sendRedirect("login.jsp");
        return;
    }
    String username  = (String) s.getAttribute("user");
    String loginTime = (String) s.getAttribute("loginTime");
    String sessionId = session.getId();
    String shortId   = sessionId.length() > 8
                       ? sessionId.substring(0, 8) + "..."
                       : sessionId;
%>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard — Student Portal</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',Arial,sans-serif;
             background:#f7f8fa;color:#1a1a2e;min-height:100vh}
        .nav{background:#fff;border-bottom:1px solid #eaecf0;
             padding:0 28px;display:flex;align-items:center;
             justify-content:space-between;height:56px}
        .brand{font-size:16px;font-weight:600;color:#1a1a2e}
        .brand b{color:#1a7a4a}
        .nav-links{display:flex;gap:4px}
        .nav-links a{color:#6b7280;text-decoration:none;
                     font-size:13px;padding:6px 12px;
                     border-radius:6px;transition:all .15s}
        .nav-links a:hover{background:#f3f4f6;color:#1a1a2e}
        .nav-links a.act{background:#e6f4ee;color:#1a7a4a;
                         font-weight:500}
        .nav-links a.out{border:1px solid #eaecf0}
        .wrap{padding:32px;max-width:960px;margin:0 auto}
        .welcome{margin-bottom:28px}
        .welcome h2{font-size:20px;font-weight:600;color:#1a1a2e}
        .welcome h2 span{color:#1a7a4a}
        .welcome p{font-size:14px;color:#9ca3af;margin-top:4px}
        .stats{display:grid;grid-template-columns:repeat(3,1fr);
               gap:14px;margin-bottom:24px}
        .stat{background:#fff;border:1px solid #eaecf0;
              border-radius:10px;padding:20px}
        .stat-label{font-size:11px;color:#9ca3af;
                    text-transform:uppercase;letter-spacing:.5px;
                    margin-bottom:8px}
        .stat-val{font-size:28px;font-weight:600;color:#1a7a4a}
        .stat-val.sm{font-size:16px;padding-top:5px;
                     font-weight:500;color:#1a1a2e}
        .stat-desc{font-size:12px;color:#c4c0b8;margin-top:4px}
        .quick{background:#fff;border:1px solid #eaecf0;
               border-radius:10px;padding:22px}
        .quick-title{font-size:14px;font-weight:600;
                     color:#1a1a2e;margin-bottom:14px}
        .quick-links{display:flex;gap:10px;flex-wrap:wrap}
        .ql{padding:10px 20px;border-radius:8px;font-size:13px;
            font-weight:500;text-decoration:none;border:none;
            cursor:pointer;transition:all .15s;display:inline-block}
        .ql-p{background:#1a7a4a;color:#fff}
        .ql-p:hover{background:#145e38}
        .ql-o{background:transparent;color:#1a7a4a;
              border:1px solid #95d5b2}
        .ql-o:hover{background:#e6f4ee}
        .ql-d{background:transparent;color:#dc2626;
              border:1px solid #fca5a5}
        .ql-d:hover{background:#fee2e2}
    </style>
</head>
<body>

    <div class="nav">
        <div class="brand">Student<b>Portal</b></div>
        <div class="nav-links">
            <a href="viewStudents.jsp">Students</a>
            <a href="welcome.jsp" class="act">Dashboard</a>
            <a href="logout.jsp" class="out">Logout</a>
        </div>
    </div>

    <div class="wrap">
        <div class="welcome">
            <h2>Welcome back, <span><%= username %></span></h2>
            <p><%= loginTime %> — here is your overview</p>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-label">Total Students</div>
                <%
                    int total = 0;
                    try {
                        java.sql.Connection c =
                            com.techhive.DBConnection.getConnection();
                        java.sql.ResultSet cr = c.createStatement()
                            .executeQuery(
                                "SELECT COUNT(*) FROM students");
                        if (cr.next()) total = cr.getInt(1);
                        cr.close(); c.close();
                    } catch(Exception e){}
                %>
                <div class="stat-val"><%= total %></div>
                <div class="stat-desc">registered in studentdb</div>
            </div>
            <div class="stat">
                <div class="stat-label">Session ID</div>
                <div class="stat-val sm"><%= shortId %></div>
                <div class="stat-desc">active session</div>
            </div>
            <div class="stat">
                <div class="stat-label">Logged In</div>
                <div class="stat-val sm">
                    <%= loginTime != null ?
                        loginTime.substring(11,16) : "--:--" %>
                </div>
                <div class="stat-desc">today</div>
            </div>
        </div>

        <div class="quick">
            <div class="quick-title">Quick Actions</div>
            <div class="quick-links">
                <a href="viewStudents.jsp" class="ql ql-p">
                    View Students
                </a>
                <a href="viewStudents.jsp" class="ql ql-o"
                   onclick="event.preventDefault();
                   sessionStorage.setItem('openAdd','1');
                   location.href='viewStudents.jsp'">
                    Add Student
                </a>
                <a href="logout.jsp" class="ql ql-d">
                    Logout
                </a>
            </div>
        </div>
    </div>
</body>
</html>