<%--
    login.jsp - login form.
    Normally shown through LoginServlet (/login), which sets:
      ${error}    - error message after a failed login
      ${username} - the username typed before (so it does not have to be retyped)
    URL parameters:  ?logout=1  after logout,  ?required=1  when a protected page was blocked.
--%>
<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<c:set var="pageTitle" value="Login"/>
<%@ include file="/WEB-INF/jspf/header.jspf" %>

<section class="card login-card">
    <h2 class="card-title">Portal Login</h2>
    <p class="card-subtitle">Sign in to view student profiles and documents.</p>

    <%-- Conditional messages --%>
    <c:if test="${not empty error}">
        <div class="alert alert-error" role="alert"><c:out value="${error}"/></div>
    </c:if>
    <c:if test="${param.logout == '1'}">
        <div class="alert alert-success" role="status">You have been logged out successfully.</div>
    </c:if>
    <c:if test="${param.required == '1'}">
        <div class="alert alert-info" role="status">Please log in to access that page.</div>
    </c:if>

    <%-- The form posts to LoginServlet. novalidate: our script shows the messages. --%>
    <form method="post" action="${pageContext.request.contextPath}/login" id="loginForm" novalidate>
        <div class="form-group">
            <label for="username">Username <span class="required">*</span></label>
            <input type="text" id="username" name="username" maxlength="50" autocomplete="username"
                   value="<c:out value='${username}'/>" autofocus>
            <span class="error-message" id="usernameError"></span>
        </div>

        <div class="form-group">
            <label for="password">Password <span class="required">*</span></label>
            <input type="password" id="password" name="password" autocomplete="current-password">
            <span class="error-message" id="passwordError"></span>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </div>
    </form>

    <p class="hint login-hint">Demo account (see README): <strong>admin</strong> / <strong>Admin@123</strong></p>
</section>

<script>
    // Client-side check: both fields are required. The server checks again in LoginServlet.
    document.getElementById("loginForm").addEventListener("submit", function (event) {
        var ok = true;
        ["username", "password"].forEach(function (id) {
            var input = document.getElementById(id);
            var empty = input.value.trim() === "";
            document.getElementById(id + "Error").textContent = empty ? "This field is required." : "";
            input.classList.toggle("invalid", empty);
            if (empty && ok) { input.focus(); ok = false; }
        });
        if (!ok) event.preventDefault();
    });
</script>

<%@ include file="/WEB-INF/jspf/footer.jspf" %>
