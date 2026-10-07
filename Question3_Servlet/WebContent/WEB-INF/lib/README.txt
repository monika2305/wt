Required JAR files for Question 3 (not stored in Git - add them before building)
================================================================================

Put these three files in THIS folder (WebContent\WEB-INF\lib):

1. mysql-connector-j-8.4.0.jar        (MySQL JDBC driver)
   Download from Maven Central:
   https://repo1.maven.org/maven2/com/mysql/mysql-connector-j/8.4.0/mysql-connector-j-8.4.0.jar
   (or from https://dev.mysql.com/downloads/connector/j/ -> "Platform Independent" ZIP)

2. taglibs-standard-impl-1.2.5.jar    (JSTL implementation)
3. taglibs-standard-spec-1.2.5.jar    (JSTL API)
   Both are already on your computer inside Tomcat's examples application:
   C:\xampp\tomcat\webapps\examples\WEB-INF\lib\
   Copy them from there.

Tomcat automatically adds every JAR in WEB-INF\lib to the application's classpath.
Do NOT put servlet-api.jar here - Tomcat already provides it.
