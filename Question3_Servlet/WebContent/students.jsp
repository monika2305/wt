<%--
    students.jsp - shows ALL students as cards.
    Data comes from StudentServlet (/students), which puts a
    List<Student> into the request as "students".
    Demonstrates: <c:forEach> loops, <c:choose>/<c:if> conditions,
    images served by FileServlet, and fallback content.
--%>
<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<%@ taglib prefix="fmt" uri="http://java.sun.com/jsp/jstl/fmt" %>

<%-- If someone opens students.jsp directly (without the Servlet), there is
     no data yet, so send them through StudentServlet first. --%>
<c:if test="${students == null}">
    <c:redirect url="/students"/>
</c:if>

<c:set var="pageTitle" value="Students"/>
<%@ include file="/WEB-INF/jspf/header.jspf" %>

<div class="page-heading">
    <div>
        <h2 class="card-title">Student Profiles</h2>
        <p class="card-subtitle">
            Welcome, <c:out value="${sessionScope.user.fullName}"/>.
            ${students.size()} student(s) loaded from MySQL using JDBC.
        </p>
    </div>
</div>

<c:choose>
    <%-- Fallback when the table is empty --%>
    <c:when test="${empty students}">
        <div class="card empty-state">No students found. Add students from the Question 2 PHP admin panel.</div>
    </c:when>

    <c:otherwise>
        <div class="student-grid">
            <%-- LOOP: one card per student --%>
            <c:forEach var="s" items="${students}">
                <article class="card student-card">

                    <%-- CONDITION: show the photo if it exists, otherwise the initials --%>
                    <c:choose>
                        <c:when test="${not empty s.profileImage}">
                            <img src="${pageContext.request.contextPath}/FileServlet?id=${s.profileImage.fileId}"
                                 alt="Photo of <c:out value='${s.name}'/>" class="card-photo">
                        </c:when>
                        <c:otherwise>
                            <div class="card-photo profile-placeholder" title="No profile image">
                                <c:out value="${s.initials}"/>
                            </div>
                        </c:otherwise>
                    </c:choose>

                    <h3 class="student-name"><c:out value="${s.name}"/></h3>
                    <span class="badge"><c:out value="${s.course}"/></span>

                    <dl class="details compact">
                        <dt>Roll No.</dt> <dd><c:out value="${s.rollNumber}"/></dd>
                        <dt>Email</dt>    <dd><c:out value="${s.email}"/></dd>
                        <dt>Joined</dt>   <dd><fmt:formatDate value="${s.createdAt}" pattern="dd MMM yyyy"/></dd>
                    </dl>

                    <div class="doc-list">
                        <strong>Documents</strong>
                        <c:choose>
                            <c:when test="${empty s.documents}">
                                <p class="missing-doc">No documents uploaded.</p>
                            </c:when>
                            <c:otherwise>
                                <ul>
                                    <%-- Nested LOOP over this student's documents --%>
                                    <c:forEach var="doc" items="${s.documents}">
                                        <li>
                                            <a href="${pageContext.request.contextPath}/FileServlet?id=${doc.fileId}" target="_blank" rel="noopener">
                                                <c:out value="${doc.categoryLabel}"/></a>
                                            <span class="file-meta">(<c:out value="${doc.formattedSize}"/>)</span>
                                        </li>
                                    </c:forEach>
                                </ul>
                            </c:otherwise>
                        </c:choose>
                    </div>

                    <a href="${pageContext.request.contextPath}/students?id=${s.studentId}" class="btn btn-primary btn-block">View Profile</a>
                </article>
            </c:forEach>
        </div>
    </c:otherwise>
</c:choose>

<%@ include file="/WEB-INF/jspf/footer.jspf" %>
