<%@ page language="java" contentType="text/html; charset=UTF-8" %>
<%@ page import="java.sql.*" %>
<%@ page import="com.techhive.DBConnection" %>
<%
    HttpSession s = request.getSession(false);
    if (s == null || s.getAttribute("user") == null) {
        response.sendRedirect("login.jsp");
        return;
    }
    String search = request.getParameter("search");
    if (search == null) search = "";
    String msg = request.getParameter("msg");
%>
<!DOCTYPE html>
<html>
<head>
    <title>Students — Student Portal</title>
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
        .wrap{padding:28px 32px;max-width:1100px;margin:0 auto}
        .top{display:flex;align-items:center;
             justify-content:space-between;margin-bottom:20px}
        .title{font-size:19px;font-weight:600;color:#1a1a2e}
        .title span{color:#1a7a4a}
        .sub{font-size:12px;color:#9ca3af;margin-top:3px;
             text-transform:uppercase;letter-spacing:.4px}
        .btn-add{background:#1a7a4a;color:#fff;border:none;
                 padding:10px 20px;border-radius:8px;font-size:14px;
                 font-weight:500;cursor:pointer;transition:background .15s}
        .btn-add:hover{background:#145e38}
        .toolbar{display:flex;gap:10px;margin-bottom:18px}
        .s-input{flex:1;max-width:320px;padding:10px 14px;
                 border:1px solid #e5e7eb;border-radius:8px;
                 font-size:14px;color:#1a1a2e;background:transparent;
                 outline:none;transition:border .15s}
        .s-input:focus{border-color:#1a7a4a}
        .s-input::placeholder{color:#d1d5db}
        .btn-s{background:#1a7a4a;color:#fff;border:none;
               padding:10px 18px;border-radius:8px;
               font-size:14px;cursor:pointer}
        .btn-cl{background:transparent;color:#6b7280;
                border:1px solid #e5e7eb;padding:10px 14px;
                border-radius:8px;font-size:14px;
                text-decoration:none;display:inline-block}
        .alert{background:#e6f4ee;border:1px solid #95d5b2;
               color:#1a5c37;padding:11px 14px;border-radius:8px;
               margin-bottom:16px;font-size:14px;font-weight:500}
        .card{background:#fff;border:1px solid #eaecf0;
              border-radius:12px;overflow:hidden}
        table{width:100%;border-collapse:collapse}
        thead th{padding:13px 18px;text-align:left;font-size:11px;
                 font-weight:600;color:#9ca3af;text-transform:uppercase;
                 letter-spacing:.7px;background:#fafafa;
                 border-bottom:1px solid #f3f4f6}
        tbody tr{border-bottom:1px solid #f9fafb;
                 transition:background .1s}
        tbody tr:hover{background:#f9fffe}
        tbody td{padding:14px 18px;font-size:14px;color:#6b7280}
        .num{font-size:13px;font-weight:600;color:#c4c0b8;width:40px}
        .name-cell{display:flex;align-items:center;gap:10px}
        .av{width:32px;height:32px;border-radius:50%;
            background:#e6f4ee;display:flex;align-items:center;
            justify-content:center;font-size:11px;font-weight:600;
            color:#1a7a4a;flex-shrink:0;text-transform:uppercase}
        .sname{color:#1a1a2e;font-weight:500;font-size:14px}
        .badge{padding:3px 10px;border-radius:20px;
               font-size:12px;font-weight:600}
        .bit{background:#dbeafe;color:#1e40af}
        .bcs{background:#e6f4ee;color:#1a5c37}
        .bbit{background:#ede9fe;color:#5b21b6}
        .other{background:#f3f4f6;color:#6b7280}
        .acts{display:flex;gap:7px}
        .ed{padding:6px 14px;background:transparent;
            color:#1a7a4a;border:1px solid #95d5b2;
            border-radius:6px;font-size:12px;font-weight:500;
            text-decoration:none;transition:all .15s}
        .ed:hover{background:#e6f4ee}
        .dl{padding:6px 14px;background:transparent;
            color:#dc2626;border:1px solid #fca5a5;
            border-radius:6px;font-size:12px;font-weight:500;
            text-decoration:none;transition:all .15s}
        .dl:hover{background:#fee2e2}
        .empty{text-align:center;padding:40px;
               color:#9ca3af;font-size:14px}
        /* Modal */
        .overlay{position:fixed;inset:0;
                 background:rgba(10,20,15,.45);
                 backdrop-filter:blur(4px);
                 display:none;align-items:center;
                 justify-content:center;z-index:100}
        .overlay.show{display:flex}
        .modal{background:#fff;border-radius:14px;
       width:440px;max-width:calc(100vw - 48px);
       padding:32px;overflow:hidden;
       box-shadow:0 24px 64px rgba(0,0,0,.12)}
        .modal-title{font-size:17px;font-weight:600;
                     color:#1a1a2e;margin-bottom:5px}
        .modal-sub{font-size:14px;color:#9ca3af;margin-bottom:24px}
        .form-row{display:grid;grid-template-columns:1fr 1fr;
          gap:14px;width:100%;overflow:hidden}
        .fg{display:flex;flex-direction:column;
   		 gap:6px;margin-bottom:16px;min-width:0}
        .fg label{font-size:11px;font-weight:600;color:#9ca3af;
                  text-transform:uppercase;letter-spacing:.5px}
        .fg input{padding:11px 13px;border:1px solid #e5e7eb;
          border-radius:8px;font-size:14px;color:#1a1a2e;
          background:transparent;outline:none;
          transition:border .15s;width:100%;min-width:0}
        .fg input:focus{border-color:#1a7a4a;
                        box-shadow:0 0 0 3px rgba(26,122,74,.08)}
        .fg input::placeholder{color:#d1d5db}
        .modal-actions{display:flex;justify-content:flex-end;
                       gap:10px;margin-top:8px}
        .btn-cancel{background:transparent;color:#6b7280;
                    border:1px solid #e5e7eb;padding:10px 18px;
                    border-radius:8px;font-size:14px;cursor:pointer}
        .btn-cancel:hover{background:#f9fafb}
        .btn-submit{background:#1a7a4a;color:#fff;border:none;
                    padding:10px 22px;border-radius:8px;font-size:14px;
                    font-weight:500;cursor:pointer}
        .btn-submit:hover{background:#145e38}
    </style>
</head>
<body>

<!-- Add Student Modal -->
<div class="overlay" id="addModal">
    <div class="modal">
        <div class="modal-title">Add New Student</div>
        <div class="modal-sub">Fill in the student details below</div>
        <form method="POST" action="StudentServlet?action=add">
            <div class="form-row">
                <div class="fg">
                    <label>Full Name</label>
                    <input type="text" name="name"
                           placeholder="e.g. Immaculate Muli"
                           required>
                </div>
                <div class="fg">
                    <label>Course</label>
                    <input type="text" name="course"
                           placeholder="e.g. BIT" required>
                </div>
            </div>
            <div class="fg">
                <label>Email Address</label>
                <input type="email" name="email"
                       placeholder="e.g. name@mku.ac.ke" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel"
                        onclick="closeModal('addModal')">
                    Cancel
                </button>
                <button type="submit" class="btn-submit">
                    Add Student
                </button>
            </div>
        </form>
    </div>
</div>

<div class="nav">
    <div class="brand">Student<b>Portal</b></div>
    <div class="nav-links">
        <a href="viewStudents.jsp" class="act">Students</a>
        <a href="welcome.jsp">Dashboard</a>
        <a href="logout.jsp" class="out">Logout</a>
    </div>
</div>

<div class="wrap">
    <div class="top">
        <div>
            <div class="title">All <span>Students</span></div>
            <%
                int total = 0;
                Connection cc = null;
                try {
                    cc = DBConnection.getConnection();
                    ResultSet cr = cc.createStatement()
                        .executeQuery("SELECT COUNT(*) FROM students");
                    if (cr.next()) total = cr.getInt(1);
                    cr.close();
                } catch(Exception e){}
                finally{ if(cc!=null) cc.close(); }
            %>
            <div class="sub"><%= total %> records</div>
        </div>
        <button class="btn-add" onclick="openModal('addModal')">
            + Add Student
        </button>
    </div>

    <% if (msg != null) { %>
        <div class="alert"><%= msg %></div>
    <% } %>

    <form class="toolbar" method="GET" action="viewStudents.jsp">
        <input type="text" class="s-input" name="search"
               placeholder="Search by name or course..."
               value="<%= search %>">
        <button type="submit" class="btn-s">Search</button>
        <% if (!search.isEmpty()) { %>
            <a href="viewStudents.jsp" class="btn-cl">Clear</a>
        <% } %>
    </form>

    <div class="card">
        <table>
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
                Connection conn = null;
                try {
                    conn = DBConnection.getConnection();
                    String sql;
                    PreparedStatement ps;
                    if (!search.isEmpty()) {
                        sql = "SELECT * FROM students WHERE " +
                              "name LIKE ? OR course LIKE ? " +
                              "ORDER BY id ASC";
                        ps = conn.prepareStatement(sql);
                        ps.setString(1,"%" + search + "%");
                        ps.setString(2,"%" + search + "%");
                    } else {
                        sql = "SELECT * FROM students ORDER BY id ASC";
                        ps = conn.prepareStatement(sql);
                    }
                    ResultSet rs = ps.executeQuery();
                    boolean hasData = false;
                    int rowNum = 1;
                    while (rs.next()) {
                        hasData = true;
                        String fullName = rs.getString("name");
                        String course   = rs.getString("course");
                        String email    = rs.getString("email");
                        int    id       = rs.getInt("id");
                        String[] parts  = fullName.trim().split(" ");
                        String initials = parts.length >= 2
                            ? "" + parts[0].charAt(0)
                              + parts[parts.length-1].charAt(0)
                            : fullName.substring(0,
                              Math.min(2, fullName.length()));
                        String cu = course.toUpperCase();
                        String bc = cu.contains("BBIT") ? "bbit"
                                  : cu.contains("BIT")  ? "bit"
                                  : cu.contains("BCS")  ? "bcs"
                                  : "other";
            %>
                <tr>
                    <td class="num"><%= rowNum++ %></td>
                    <td>
                        <div class="name-cell">
                            <div class="av"><%= initials %></div>
                            <span class="sname"><%= fullName %></span>
                        </div>
                    </td>
                    <td>
                        <span class="badge <%= bc %>">
                            <%= course %>
                        </span>
                    </td>
                    <td><%= email %></td>
                    <td>
                        <div class="acts">
                            <a href="editStudent.jsp?id=<%=id%>"
                               class="ed">Edit</a>
                            <a href="deleteStudent.jsp?id=<%=id%>"
                               class="dl"
                               onclick="return confirm(
                               'Delete this student?')">
                               Delete
                            </a>
                        </div>
                    </td>
                </tr>
            <%
                    }
                    if (!hasData) {
            %>
                <tr>
                    <td colspan="5" class="empty">
                        <%= search.isEmpty() ?
                            "No students found." :
                            "No results for \"" + search + "\"" %>
                    </td>
                </tr>
            <%
                    }
                    rs.close(); ps.close();
                } catch(Exception e) {
            %>
                <tr>
                    <td colspan="5" class="empty">
                        Error: <%= e.getMessage() %>
                    </td>
                </tr>
            <%
                } finally {
                    if (conn != null) conn.close();
                }
            %>
            </tbody>
        </table>
    </div>
</div>

<script>
function openModal(id){
    document.getElementById(id).classList.add('show');
}
function closeModal(id){
    document.getElementById(id).classList.remove('show');
}
document.querySelectorAll('.overlay').forEach(function(o){
    o.addEventListener('click',function(e){
        if(e.target===o) o.classList.remove('show');
    });
});
if(sessionStorage.getItem('openAdd')==='1'){
    sessionStorage.removeItem('openAdd');
    openModal('addModal');
}
</script>
</body>
</html>