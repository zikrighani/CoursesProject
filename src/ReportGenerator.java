import java.util.*;

public class ReportGenerator {
    private StudentManager studentManager;

    public ReportGenerator(StudentManager studentManager) {
        this.studentManager = studentManager;
    }

    // Generate student list by course
    public void generateStudentListByCourse() {
        Student[] allStudents = studentManager.getAllStudents();
        if (allStudents.length == 0) {
            System.out.println("No students found in the system.");
            return;
        }

        // Get unique courses
        Set<String> courses = new HashSet<>();
        for (Student student : allStudents) {
            courses.add(student.getCourse().toUpperCase());
        }

        System.out.println("\n=== STUDENT LIST BY COURSE ===");
        for (String course : courses) {
            System.out.println("\nCourse: " + course);
            System.out.println("----------------------------------------");
            int count = 0;
            for (Student student : allStudents) {
                if (student.getCourse().equalsIgnoreCase(course)) {
                    count++;
                    System.out.println(count + ". " + student.getName() + " (" + student.getStudentID() + ")");
                }
            }
            System.out.println("Total students in " + course + ": " + count);
        }
    }

    // Generate performance report
    public void generatePerformanceReport() {
        Student[] allStudents = studentManager.getAllStudents();
        if (allStudents.length == 0) {
            System.out.println("No students found in the system.");
            return;
        }

        System.out.println("\n=== PERFORMANCE REPORT ===");

        // Grade distribution
        int[] gradeCount = new int[5]; // A, B, C, D, F
        double totalGrade = 0;
        double highestGrade = 0;
        double lowestGrade = 100;
        String topStudent = "";
        String lowStudent = "";

        for (Student student : allStudents) {
            double grade = student.getGrade();
            totalGrade += grade;

            if (grade > highestGrade) {
                highestGrade = grade;
                topStudent = student.getName();
            }
            if (grade < lowestGrade) {
                lowestGrade = grade;
                lowStudent = student.getName();
            }

            // Count grade distribution
            if (grade >= 75) gradeCount[0]++;
            else if (grade >= 60) gradeCount[1]++;
            else if (grade >= 47) gradeCount[2]++;
            else if (grade >= 40) gradeCount[3]++;
            else gradeCount[4]++;
        }

        double averageGrade = totalGrade / allStudents.length;

        System.out.println("Total Students: " + allStudents.length);
        System.out.println("Average Grade: " + String.format("%.2f", averageGrade));
        System.out.println("Highest Grade: " + String.format("%.2f", highestGrade) + " (" + topStudent + ")");
        System.out.println("Lowest Grade: " + String.format("%.2f", lowestGrade) + " (" + lowStudent + ")");

        System.out.println("\nGrade Distribution:");
        System.out.println("A (75-100): " + gradeCount[0] + " students");
        System.out.println("B (60-74):  " + gradeCount[1] + " students");
        System.out.println("C (47-59):  " + gradeCount[2] + " students");
        System.out.println("D (40-46):  " + gradeCount[3] + " students");
        System.out.println("F (0-39):   " + gradeCount[4] + " students");
    }

    // Generate enrollment statistics
    public void generateEnrollmentStatistics() {
        Student[] allStudents = studentManager.getAllStudents();
        if (allStudents.length == 0) {
            System.out.println("No students found in the system.");
            return;
        }

        System.out.println("\n=== ENROLLMENT STATISTICS ===");

        // Course enrollment count
        Map<String, Integer> courseCount = new HashMap<>();
        Map<String, Integer> ageGroups = new HashMap<>();
        ageGroups.put("18-20", 0);
        ageGroups.put("21-25", 0);
        ageGroups.put("26-30", 0);
        ageGroups.put("30+", 0);

        int undergraduateCount = 0;
        int graduateCount = 0;
        int regularCount = 0;

        for (Student student : allStudents) {
            // Course count
            String course = student.getCourse().toUpperCase();
            courseCount.put(course, courseCount.getOrDefault(course, 0) + 1);

            // Age group count
            int age = student.getAge();
            if (age >= 18 && age <= 20) {
                ageGroups.put("18-20", ageGroups.get("18-20") + 1);
            } else if (age >= 21 && age <= 25) {
                ageGroups.put("21-25", ageGroups.get("21-25") + 1);
            } else if (age >= 26 && age <= 30) {
                ageGroups.put("26-30", ageGroups.get("26-30") + 1);
            } else {
                ageGroups.put("30+", ageGroups.get("30+") + 1);
            }

            // Student type count
            if (student instanceof UndergraduateStudent) {
                undergraduateCount++;
            } else if (student instanceof GraduateStudent) {
                graduateCount++;
            } else {
                regularCount++;
            }
        }

        System.out.println("Total Enrollment: " + allStudents.length);

        System.out.println("\nEnrollment by Course:");
        for (Map.Entry<String, Integer> entry : courseCount.entrySet()) {
            double percentage = (entry.getValue() * 100.0) / allStudents.length;
            System.out.println(entry.getKey() + ": " + entry.getValue() +
                    " students (" + String.format("%.1f", percentage) + "%)");
        }

        System.out.println("\nAge Distribution:");
        for (Map.Entry<String, Integer> entry : ageGroups.entrySet()) {
            double percentage = (entry.getValue() * 100.0) / allStudents.length;
            System.out.println(entry.getKey() + " years: " + entry.getValue() +
                    " students (" + String.format("%.1f", percentage) + "%)");
        }

        System.out.println("\nStudent Type Distribution:");
        System.out.println("Undergraduate: " + undergraduateCount + " students");
        System.out.println("Graduate: " + graduateCount + " students");
        System.out.println("Regular: " + regularCount + " students");
    }

    // Generate individual transcript
    public void generateTranscript(String studentID) {
        Student student = studentManager.searchByID(studentID);
        if (student == null) {
            System.out.println("Student not found!");
            return;
        }

        System.out.println("\n=== OFFICIAL TRANSCRIPT ===");
        System.out.println("Student ID: " + student.getStudentID());
        System.out.println("Name: " + student.getName());
        System.out.println("Age: " + student.getAge());
        System.out.println("Course: " + student.getCourse());
        System.out.println("Phone: " + student.getPhoneNo());
        System.out.println("Address: " + student.getAddress());

        if (student instanceof UndergraduateStudent) {
            UndergraduateStudent us = (UndergraduateStudent) student;
            System.out.println("Student Type: Undergraduate");
            System.out.println("Major: " + us.getMajor());
            System.out.println("Year Level: " + us.getYearLevel());
        } else if (student instanceof GraduateStudent) {
            GraduateStudent gs = (GraduateStudent) student;
            System.out.println("Student Type: Graduate");
            System.out.println("Research Area: " + gs.getResearchArea());
            System.out.println("Supervisor: " + gs.getSupervisor());
        } else {
            System.out.println("Student Type: Regular");
        }

        System.out.println("\nACADEMIC RECORD:");
        System.out.println("Current Grade: " + String.format("%.2f", student.getGrade()));
        System.out.println("Letter Grade: " + student.getGradeLetter());

        // Grade status
        if (student.getGrade() >= 65) {
            System.out.println("Status: PASSING");
        } else {
            System.out.println("Status: FAILING");
        }

        System.out.println("\nGenerated on: " + new Date());
        System.out.println("=================================");
    }
}