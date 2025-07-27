import java.io.*;
import java.util.*;

public class StudentManager {
    private Student[] students;
    private int studentCount;
    private final int MAX_STUDENTS = 100;

    public StudentManager() {
        students = new Student[MAX_STUDENTS];
        studentCount = 0;
    }

    // Add student method
    public boolean addStudent(Student student) {
        if (studentCount < MAX_STUDENTS) {
            // Check for duplicate ID
            for (int i = 0; i < studentCount; i++) {
                if (students[i].getStudentID().equals(student.getStudentID())) {
                    System.out.println("Error: Student ID already exists!");
                    return false;
                }
            }
            students[studentCount] = student;
            studentCount++;
            System.out.println("Student added successfully!");
            return true;
        } else {
            System.out.println("Error: Maximum student capacity reached!");
            return false;
        }
    }

    // Search student by ID
    public Student searchByID(String studentID) {
        for (int i = 0; i < studentCount; i++) {
            if (students[i].getStudentID().equalsIgnoreCase(studentID)) {
                return students[i];
            }
        }
        return null;
    }

    // Search students by name
    public Student[] searchByName(String name) {
        Student[] results = new Student[MAX_STUDENTS];
        int resultCount = 0;

        for (int i = 0; i < studentCount; i++) {
            if (students[i].getName().toLowerCase().contains(name.toLowerCase())) {
                results[resultCount] = students[i];
                resultCount++;
            }
        }

        if (resultCount == 0) {
            return null;
        }

        // Create array with exact size
        Student[] finalResults = new Student[resultCount];
        for (int i = 0; i < resultCount; i++) {
            finalResults[i] = results[i];
        }
        return finalResults;
    }

    // Search students by course
    public Student[] searchByCourse(String course) {
        Student[] results = new Student[MAX_STUDENTS];
        int resultCount = 0;

        for (int i = 0; i < studentCount; i++) {
            if (students[i].getCourse().equalsIgnoreCase(course)) {
                results[resultCount] = students[i];
                resultCount++;
            }
        }

        if (resultCount == 0) {
            return null;
        }

        Student[] finalResults = new Student[resultCount];
        for (int i = 0; i < resultCount; i++) {
            finalResults[i] = results[i];
        }
        return finalResults;
    }

    // Update student information
    public boolean updateStudent(String studentID, Student updatedStudent) {
        for (int i = 0; i < studentCount; i++) {
            if (students[i].getStudentID().equalsIgnoreCase(studentID)) {
                students[i] = updatedStudent;
                System.out.println("Student updated successfully!");
                return true;
            }
        }
        System.out.println("Error: Student not found!");
        return false;
    }

    // Delete student
    public boolean deleteStudent(String studentID) {
        for (int i = 0; i < studentCount; i++) {
            if (students[i].getStudentID().equalsIgnoreCase(studentID)) {
                // Shift all elements to the left
                for (int j = i; j < studentCount - 1; j++) {
                    students[j] = students[j + 1];
                }
                studentCount--;
                students[studentCount] = null; // Clear the last position
                System.out.println("Student deleted successfully!");
                return true;
            }
        }
        System.out.println("Error: Student not found!");
        return false;
    }

    // Display all students
    public void displayAllStudents() {
        if (studentCount == 0) {
            System.out.println("No students found in the system.");
            return;
        }

        System.out.println("\n=== ALL STUDENTS ===");
        for (int i = 0; i < studentCount; i++) {
            System.out.println("Student " + (i + 1) + ":");
            students[i].displayStudent();
        }
    }

    // Get student count
    public int getStudentCount() {
        return studentCount;
    }

    // Get all students
    public Student[] getAllStudents() {
        Student[] result = new Student[studentCount];
        for (int i = 0; i < studentCount; i++) {
            result[i] = students[i];
        }
        return result;
    }

    // Save students to file
    public void saveToFile(String filename) {
        try {
            PrintWriter writer = new PrintWriter(new FileWriter(filename));
            for (int i = 0; i < studentCount; i++) {
                Student s = students[i];
                if (s instanceof UndergraduateStudent) {
                    UndergraduateStudent us = (UndergraduateStudent) s;
                    writer.println("UNDERGRAD|" + s.getStudentID() + "|" + s.getName() + "|" +
                            s.getAge() + "|" + s.getCourse() + "|" + s.getGrade() + "|" +
                            s.getPhoneNo() + "|" + s.getAddress() + "|" + us.getMajor() + "|" +
                            us.getYearLevel());
                } else if (s instanceof GraduateStudent) {
                    GraduateStudent gs = (GraduateStudent) s;
                    writer.println("GRADUATE|" + s.getStudentID() + "|" + s.getName() + "|" +
                            s.getAge() + "|" + s.getCourse() + "|" + s.getGrade() + "|" +
                            s.getPhoneNo() + "|" + s.getAddress() + "|" + gs.getResearchArea() + "|" +
                            gs.getSupervisor());
                } else {
                    writer.println("REGULAR|" + s.getStudentID() + "|" + s.getName() + "|" +
                            s.getAge() + "|" + s.getCourse() + "|" + s.getGrade() + "|" +
                            s.getPhoneNo() + "|" + s.getAddress());
                }
            }
            writer.close();
            System.out.println("Data saved to " + filename + " successfully!");
        } catch (IOException e) {
            System.out.println("Error saving to file: " + e.getMessage());
        }
    }

    // Load students from file
    public void loadFromFile(String filename) {
        try {
            BufferedReader reader = new BufferedReader(new FileReader(filename));
            String line;
            studentCount = 0; // Reset count

            while ((line = reader.readLine()) != null && studentCount < MAX_STUDENTS) {
                String[] parts = line.split("\\|");
                if (parts.length >= 8) {
                    String type = parts[0];
                    String id = parts[1];
                    String name = parts[2];
                    int age = Integer.parseInt(parts[3]);
                    String course = parts[4];
                    double grade = Double.parseDouble(parts[5]);
                    String phone = parts[6];
                    String address = parts[7];

                    if (type.equals("UNDERGRAD") && parts.length >= 10) {
                        String major = parts[8];
                        int yearLevel = Integer.parseInt(parts[9]);
                        students[studentCount] = new UndergraduateStudent(id, name, age, course,
                                grade, phone, address, major, yearLevel);
                    } else if (type.equals("GRADUATE") && parts.length >= 10) {
                        String researchArea = parts[8];
                        String supervisor = parts[9];
                        students[studentCount] = new GraduateStudent(id, name, age, course,
                                grade, phone, address, researchArea, supervisor);
                    } else {
                        students[studentCount] = new Student(id, name, age, course, grade, phone, address);
                    }
                    studentCount++;
                }
            }
            reader.close();
            System.out.println("Data loaded from " + filename + " successfully!");
            System.out.println("Total students loaded: " + studentCount);
        } catch (FileNotFoundException e) {
            System.out.println("File not found. Starting with empty database.");
        } catch (IOException e) {
            System.out.println("Error reading from file: " + e.getMessage());
        } catch (NumberFormatException e) {
            System.out.println("Error parsing data from file: " + e.getMessage());
        }
    }
}