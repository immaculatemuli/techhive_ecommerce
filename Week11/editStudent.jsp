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
    if (id != null) id = id.trim();
    String name="", course="", email="", err="";
    Connection conn = null;
    try {
        conn = DBConnection.getConnection();
        PreparedStatement ps = conn.prepareStatement(
            "SELECT * FROM students WHERE id=?");
        ps.setInt(1, Integer.parseInt(id));
        ResultSet rs = ps.executeQuery();
        if (rs.next()) {
            name   = rs.getString("name");
            course = rs.getString("course");
            email  = rs.getString("email");
        }
        rs.close(); ps.close();
    } catch(Exception e) {
        err = e.getMessage();
    } finally {
        if (conn != null) conn.close();
    }
%>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Student — Student Portal</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',Arial,sans-serif;
             background:#f7f8fa;color:#1a1a2e;min-height:100vh}

        .nav{background:#fff;border-bottom:1px solid #eaecf0;
             padding:0 28px;display:flex;align-items:center;
             justify-content:space-between;height:56px;
             position:relative;z-index:1}
        .brand{font-size:16px;font-weight:600;color:#1a1a2e}
        .brand b{color:#1a7a4a}
        .nav-links{display:flex;gap:4px}
        .nav-links a{color:#6b7280;text-decoration:none;
                     font-size:13px;padding:6px 12px;
                     border-radius:6px;transition:all .15s}
        .nav-links a.act{background:#e6f4ee;color:#1a7a4a;
                         font-weight:500}
        .nav-links a.out{border:1px solid #eaecf0}

        .bg-content{padding:28px 32px;max-width:1100px;
                    margin:0 auto;filter:blur(2px);
                    pointer-events:none;user-select:none;opacity:.6}
        .bg-top{display:flex;align-items:center;
                justify-content:space-between;margin-bottom:20px}
        .bg-title{font-size:19px;font-weight:600;color:#1a1a2e}
        .bg-title span{color:#1a7a4a}
        .bg-sub{font-size:12px;color:#9ca3af;margin-top:3px;
                text-transform:uppercase;letter-spacing:.4px}
        .bg-btn{background:#1a7a4a;color:#fff;border:none;
                padding:10px 20px;border-radius:8px;font-size:14px}
        .bg-toolbar{display:flex;gap:10px;margin-bottom:18px}
        .bg-input{flex:1;max-width:320px;padding:10px 14px;
                  border:1px solid #e5e7eb;border-radius:8px;
                  font-size:14px;background:transparent}
        .bg-btn-s{background:#1a7a4a;color:#fff;border:none;
                  padding:10px 18px;border-radius:8px;font-size:14px}
        .bg-card{background:#fff;border:1px solid #eaecf0;
                 border-radius:12px;overflow:hidden}
        .bg-table{width:100%;border-collapse:collapse}
        .bg-table thead th{padding:13px 18px;text-align:left;
                           font-size:11px;font-weight:600;
                           color:#9ca3af;text-transform:uppercase;
                           background:#fafafa;
                           border-bottom:1px solid #f3f4f6}
        .bg-table tbody tr{border-bottom:1px solid #f9fafb}
        .bg-table tbody td{padding:14px 18px;font-size:14px;
                           color:#6b7280}
        .bg-num{font-size:13px;font-weight:600;color:#c4c0b8}
        .bg-nc{display:flex;align-items:center;gap:10px}
        .bg-av{width:32px;height:32px;border-radius:50%;
               background:#e6f4ee;display:flex;align-items:center;
               justify-content:center;font-size:11px;
               font-weight:600;color:#1a7a4a;flex-shrink:0}
        .bg-name{color:#1a1a2e;font-weight:500;font-size:14px}
        .bg-badge{padding:3px 10px;border-radius:20px;
                  font-size:12px;font-weight:600}
        .bg-bit{background:#dbeafe;color:#1e40af}
        .bg-bcs{background:#e6f4ee;color:#1a5c37}
        .bg-bbit{background:#ede9fe;color:#5b21b6}
        .bg-acts{display:flex;gap:7px}
        .bg-ed{padding:6px 14px;background:transparent;
               color:#1a7a4a;border:1px solid #95d5b2;
               border-radius:6px;font-size:12px}
        .bg-dl{padding:6px 14px;background:transparent;
               color:#dc2626;border:1px solid #fca5a5;
               border-radius:6px;font-size:12px}

        .overlay{position:fixed;inset:0;
                 background:rgba(10,20,15,.42);
                 backdrop-filter:blur(3px);
                 display:flex;align-items:center;
                 justify-content:center;z-index:200}
        .modal{background:#fff;border-radius:14px;
               width:440px;max-width:calc(100vw - 48px);
               padding:32px;
               box-shadow:0 24px 64px rgba(0,0,0,.14)}
        .modal-title{font-size:17px;font-weight:600;
                     color:#1a1a2e;margin-bottom:5px}
        .modal-sub{font-size:14px;color:#9ca3af;margin-bottom:24px}
        .form-row{display:grid;
                  grid-template-columns:1fr 1fr;
                  gap:14px;width:100%;overflow:hidden}
        .fg{display:flex;flex-direction:column;
            gap:6px;margin-bottom:16px;min-width:0}
        .fg label{font-size:11px;font-weight:600;color:#9ca3af;
                  text-transform:uppercase;letter-spacing:.5px}
        .fg input{padding:11px 13px;border:1px solid #e5e7eb;
                  border-radius:8px;font-size:14px;color:#1a1a2e;
                  background:transparent;outline:none;
                  transition:border .15s;
                  width:100%;min-width:0}
        .fg input:focus{border-color:#1a7a4a;
                        box-shadow:0 0 0 3px rgba(26,122,74,.08)}
        .fg input::placeholder{color:#d1d5db}
        .err{background:#fee2e2;border:1px solid #fca5a5;
             color:#dc2626;padding:10px 14px;border-radius:8px;
             margin-bottom:16px;font-size:13px}
        .modal-actions{display:flex;justify-content:flex-end;
                       gap:10px;margin-top:8px}
        .btn-cancel{background:transparent;color:#6b7280;
                    border:1px solid #e5e7eb;padding:10px 18px;
                    border-radius:8px;font-size:14px;
                    text-decoration:none;display:inline-block;
                    transition:background .15s}
        .btn-cancel:hover{background:#f9fafb}
        .btn-submit{background:#1a7a4a;color:#fff;border:none;
                    padding:10px 22px;border-radius:8px;font-size:14px;
                    font-weight:500;cursor:pointer;
                    transition:background .15s}
        .btn-submit:hover{background:#145e38}
    </style>
</head>
<body>

    <div class="nav">
        <div class="brand">Student<b>Portal</b></div>
        <div class="nav-links">
            <a href="viewStudents.jsp" class="act">Students</a>
            <a href="welcome.jsp">Dashboard</a>
            <a href="logout.jsp" class="out">Logout</a>
        </div>
    </div>

    <div class="bg-content">
        <div class="bg-top">
            <div>
                <div class="bg-title">All <span>Students</span></div>
                <div class="bg-sub">records</div>
            </div>
            <button class="bg-btn">+ Add Student</button>
        </div>
        <div class="bg-toolbar">
            <input class="bg-input"
                   placeholder="Search by name or course...">
            <button class="bg-btn-s">Search</button>
        </div>
        <div class="bg-card">
            <table class="bg-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <%
                    Connection bgConn = null;
                    try {
                        bgConn = DBConnection.getConnection();
                        ResultSet bgRs = bgConn.createStatement()
                            .executeQuery(
                                "SELECT * FROM students ORDER BY id ASC");
                        int rn = 1;
                        while (bgRs.next()) {
                            String fn = bgRs.getString("name");
                            String co = bgRs.getString("course");
                            String em = bgRs.getString("email");
                            String[] pp = fn.trim().split(" ");
                            String ini = pp.length >= 2
                                ? "" + pp[0].charAt(0)
                                  + pp[pp.length-1].charAt(0)
                                : fn.substring(0,
                                  Math.min(2, fn.length()));
                            String cu = co.toUpperCase();
                            String bc = cu.contains("BBIT") ? "bg-bbit"
                                      : cu.contains("BIT")  ? "bg-bit"
                                      : cu.contains("BCS")  ? "bg-bcs"
                                      : "";
                %>
                    <tr>
                        <td class="bg-num"><%= rn++ %></td>
                        <td>
                            <div class="bg-nc">
                                <div class="bg-av">
                                    <%= ini.toUpperCase() %>
                                </div>
                                <span class="bg-name"><%= fn %></span>
                            </div>
                        </td>
                        <td>
                            <span class="bg-badge <%= bc %>">
                                <%= co %>
                            </span>
                        </td>
                        <td><%= em %></td>
                        <td>
                            <div class="bg-acts">
                                <span class="bg-ed">Edit</span>
                                <span class="bg-dl">Delete</span>
                            </div>
                        </td>
                    </tr>
                <%
                        }
                        bgRs.close();
                    } catch(Exception e){}
                    finally{
                        if(bgConn!=null) bgConn.close();
                    }
                %>
                </tbody>
            </table>
        </div>
    </div>

    <div class="overlay">
        <div class="modal">
            <div class="modal-title">Edit Student</div>
            <div class="modal-sub">
                Update the student details below
            </div>
            <% if (!err.isEmpty()) { %>
                <div class="err"><%= err %></div>
            <% } %>
            <form method="POST" action="StudentServlet?action=edit">
                <input type="hidden" name="id" value="<%= id %>">
                <div class="form-row">
                    <div class="fg">
                        <label>Full Name</label>
                        <input type="text" name="name"
                               value="<%= name %>"
                               placeholder="Full name" required>
                    </div>
                    <div class="fg">
                        <label>Course</label>
                        <input type="text" name="course"
                               value="<%= course %>"
                               placeholder="e.g. BIT" required>
                    </div>
                </div>
                <div class="fg">
                    <label>Email Address</label>
                    <input type="email" name="email"
                           value="<%= email %>"
                           placeholder="e.g. name@mku.ac.ke" required>
                </div>
                <div class="modal-actions">
                    <a href="viewStudents.jsp" class="btn-cancel">
                        Cancel
                    </a>
                    <button type="submit" class="btn-submit">
                        Update Student
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>