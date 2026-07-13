import java.io.*;
import java.util.Date;
import jakarta.servlet.*;
import jakarta.servlet.http.*;
import jakarta.servlet.annotation.*;

@WebServlet("/LoginServlet")
public class LoginServlet extends HttpServlet {

    protected void doPost(HttpServletRequest request,
                          HttpServletResponse response)
            throws ServletException, IOException {

        String username = request.getParameter("username");
        String password = request.getParameter("password");
        String rememberMe = request.getParameter("rememberMe");

        if (username != null && !username.isEmpty()
                && username.equals("admin")
                && "1234".equals(password)) {

            // Create session
            HttpSession session = request.getSession();
            session.setAttribute("user", username);
            session.setAttribute("loginTime",
                new Date().toString());

            // Handle Remember Me cookie
            if (rememberMe != null) {
                // Remember Me checked — save username in cookie
                Cookie rememberCookie =
                    new Cookie("rememberedUser", username);
                rememberCookie.setMaxAge(60 * 60 * 24 * 30);
                response.addCookie(rememberCookie);
            } else {
                // Remember Me not checked — delete cookie
                Cookie rememberCookie =
                    new Cookie("rememberedUser", "");
                rememberCookie.setMaxAge(0);
                response.addCookie(rememberCookie);
            }

            response.sendRedirect("welcome.jsp");

        } else {
            request.setAttribute("error",
                "Invalid username or password. Try admin / 1234");
            RequestDispatcher rd =
                request.getRequestDispatcher("login.jsp");
            rd.forward(request, response);
        }
    }
}