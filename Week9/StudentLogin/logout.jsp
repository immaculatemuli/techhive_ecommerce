<%
    HttpSession s = request.getSession(false);
    if (s != null) {
        s.invalidate();
    }
    response.sendRedirect("login.jsp");
%>