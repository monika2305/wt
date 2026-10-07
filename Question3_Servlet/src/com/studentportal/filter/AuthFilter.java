package com.studentportal.filter;

import java.io.IOException;

import javax.servlet.Filter;
import javax.servlet.FilterChain;
import javax.servlet.FilterConfig;
import javax.servlet.ServletException;
import javax.servlet.ServletRequest;
import javax.servlet.ServletResponse;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;

/**
 * Runs BEFORE every protected page (see <filter-mapping> in web.xml).
 * If there is no logged-in user in the HttpSession, the request is
 * redirected to the login page instead of reaching the Servlet/JSP.
 */
public class AuthFilter implements Filter {

    @Override
    public void init(FilterConfig filterConfig) {
        // nothing to initialise
    }

    @Override
    public void doFilter(ServletRequest req, ServletResponse res, FilterChain chain)
            throws IOException, ServletException {
        HttpServletRequest request = (HttpServletRequest) req;
        HttpServletResponse response = (HttpServletResponse) res;

        HttpSession session = request.getSession(false);   // do not create a session here
        boolean loggedIn = session != null && session.getAttribute("user") != null;

        if (!loggedIn) {
            response.sendRedirect(request.getContextPath() + "/login?required=1");
            return;   // stop: the protected page is never executed
        }

        // Tell the browser not to cache protected pages, so after logout the
        // Back button cannot show student data again.
        response.setHeader("Cache-Control", "no-cache, no-store, must-revalidate");
        response.setHeader("Pragma", "no-cache");
        response.setDateHeader("Expires", 0);

        chain.doFilter(request, response);   // logged in -> continue to the page
    }

    @Override
    public void destroy() {
        // nothing to clean up
    }
}
