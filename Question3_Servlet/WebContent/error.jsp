<%--
    error.jsp - friendly page for 404 (not found) and 500 (server error).
    Configured with <error-page> in web.xml.
--%>
<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" isErrorPage="true" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<c:set var="pageTitle" value="Error"/>
<c:set var="status" value="${requestScope['javax.servlet.error.status_code']}"/>
<%@ include file="/WEB-INF/jspf/header.jspf" %>

<section class="card confirm-card">
    <h2 class="card-title danger-text">
        <c:choose>
            <c:when test="${status == 404}">Page or file not found (404)</c:when>
            <c:when test="${status == 400}">Bad request (400)</c:when>
            <c:when test="${status == 405}">Action not allowed (405)</c:when>
            <c:otherwise>Something went wrong (${status})</c:otherwise>
        </c:choose>
    </h2>

    <%-- The message given to response.sendError(...) or the exception message --%>
    <c:if test="${not empty requestScope['javax.servlet.error.message']}">
        <p><c:out value="${requestScope['javax.servlet.error.message']}"/></p>
    </c:if>
    <c:if test="${status == 500}">
        <p class="hint">Check that MySQL is running and the student_portal database exists.</p>
    </c:if>

    <div class="form-actions">
        <a href="${pageContext.request.contextPath}/students" class="btn btn-primary">Back to students</a>
    </div>
</section>

<%@ include file="/WEB-INF/jspf/footer.jspf" %>
