<%--
    profile.jsp - full profile of ONE student.
    StudentServlet (/students?id=N) puts a Student object into the request as "student".
--%>
<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<%@ taglib prefix="fmt" uri="http://java.sun.com/jsp/jstl/fmt" %>

<%-- Opened directly without the Servlet -> go to the student list --%>
<c:if test="${student == null}">
    <c:redirect url="/students"/>
</c:if>

<c:set var="pageTitle" value="${student.name}"/>
<c:set var="ctx" value="${pageContext.request.contextPath}"/>
<%@ include file="/WEB-INF/jspf/header.jspf" %>

<p class="breadcrumb"><a href="${ctx}/students">&larr; Back to all students</a></p>

<div class="profile-layout">

    <!-- Left: photo and details -->
    <section class="card profile-card">
        <c:choose>
            <c:when test="${not empty student.profileImage}">
                <img src="${ctx}/FileServlet?id=${student.profileImage.fileId}"
                     alt="Profile photo of <c:out value='${student.name}'/>" class="profile-photo">
            </c:when>
            <c:otherwise>
                <div class="profile-photo profile-placeholder"><c:out value="${student.initials}"/></div>
                <p class="hint">No profile image uploaded.</p>
            </c:otherwise>
        </c:choose>

        <h2 class="profile-name"><c:out value="${student.name}"/></h2>
        <span class="badge"><c:out value="${student.course}"/></span>

        <dl class="details">
            <dt>Student ID</dt>  <dd>${student.studentId}</dd>
            <dt>Roll Number</dt> <dd><c:out value="${student.rollNumber}"/></dd>
            <dt>Email</dt>       <dd><c:out value="${student.email}"/></dd>
            <dt>Registered</dt>  <dd><fmt:formatDate value="${student.createdAt}" pattern="dd MMM yyyy, hh:mm a"/></dd>
        </dl>
    </section>

    <!-- Right: all uploaded files with metadata -->
    <section class="card">
        <h2 class="card-title">Uploaded Files</h2>
        <p class="card-subtitle">Files are streamed by <code>FileServlet</code> using their database ID.</p>

        <c:choose>
            <c:when test="${empty student.files}">
                <p class="empty-state">No files have been uploaded for this student.</p>
            </c:when>
            <c:otherwise>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>File ID</th>
                                <th>Category</th>
                                <th>Original name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Uploaded on</th>
                                <th>Open</th>
                            </tr>
                        </thead>
                        <tbody>
                            <c:forEach var="f" items="${student.files}">
                                <tr>
                                    <td>${f.fileId}</td>
                                    <td><c:out value="${f.categoryLabel}"/></td>
                                    <td class="break"><c:out value="${f.originalName}"/></td>
                                    <td><c:out value="${f.fileType}"/></td>
                                    <td><c:out value="${f.formattedSize}"/></td>
                                    <td><fmt:formatDate value="${f.uploadedAt}" pattern="dd MMM yyyy, hh:mm a"/></td>
                                    <td><a href="${ctx}/FileServlet?id=${f.fileId}" target="_blank" rel="noopener"
                                           class="btn btn-small btn-secondary">Open</a></td>
                                </tr>
                            </c:forEach>
                        </tbody>
                    </table>
                </div>
            </c:otherwise>
        </c:choose>

        <!-- Document previews: images are shown, PDFs get a link, missing ones a warning -->
        <h3 class="section-title">Documents</h3>
        <div class="doc-previews">
            <%-- [a, b] creates a small list (EL 3.0). Loop index 0 = ID proof, 1 = certificate.
                 A missing document is null, which the first <c:when> handles. --%>
            <c:forEach var="doc" items="${[student.idProof, student.certificate]}" varStatus="loop">
                <c:set var="label" value="${loop.index == 0 ? 'ID Proof' : 'Certificate'}"/>
                <div class="doc-box">
                    <strong><c:out value="${label}"/></strong>
                    <c:choose>
                        <c:when test="${empty doc}">
                            <p class="missing-doc">&#9888; Not uploaded.</p>
                        </c:when>
                        <c:when test="${doc.image}">
                            <a href="${ctx}/FileServlet?id=${doc.fileId}" target="_blank" rel="noopener">
                                <img src="${ctx}/FileServlet?id=${doc.fileId}" alt="<c:out value='${label}'/> preview" class="doc-thumb">
                            </a>
                        </c:when>
                        <c:otherwise>
                            <p class="pdf-icon" aria-hidden="true">PDF</p>
                            <a href="${ctx}/FileServlet?id=${doc.fileId}" target="_blank" rel="noopener" class="btn btn-small btn-primary">Open document</a>
                        </c:otherwise>
                    </c:choose>
                </div>
            </c:forEach>
        </div>
    </section>
</div>

<%@ include file="/WEB-INF/jspf/footer.jspf" %>
