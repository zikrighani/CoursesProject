import java.util.*;

public class StudentManagementApp {
    private static StudentManager studentManager = new StudentManager();
    private static ReportGenerator reportGenerator = new ReportGenerator(studentManager);
    private static Scanner scan = new Scanner(System.in);
    private static Scanner scanInt = new Scanner(System.in);
    private static final String DATA_FILE = "students.txt";

    public static void main(String[] args) {
        // Load existing data
        studentManager.loadFromFile(DATA_FILE);

        System.out.println("========================================");
        System.out.println("    STUDENT MANAGEMENT SYSTEM");
        System.out.println("========================================");

        int choice;
        do {
            displayMenu();
            System.out.print("Enter your choice: ");
            choice = scanInt.nextInt();

            switch (choice) {
                case 1:
                    addStudent();
                    break;
                case 2:
                    searchStudent();
                    break;
                case 3:
                    updateStudent();
                    break;
                case 4:
                    deleteStudent();
                    break;
                case 5:
                    displayAllStudents();
                    break;
                case 6:
                    generateReports();
                    break;
                case 7:
                    saveData();
                    break;
                case 8:
                    loadData();
                    break;
                case 0:
                    saveData(); // Auto-save before exit
                    System.out.println("Thank you for using Student Management System!");
                    break;
                default:
                    System.out.println("Invalid choice! Please try again.");
            }

            if (choice != 0) {
                System.out.println("\nPress Enter to continue...");
                try {
                    System.in.read();
                } catch (Exception e) {
                }
            }

        } while (choice != 0);
    }

    private static void displayMenu() {
        System.out.println("\n========================================");
        System.out.println("              MAIN MENU");
        System.out.println("========================================");
        System.out.println("1. Add Student");
        System.out.println("2. Search Student");
        System.out.println("3. Update Student");
        System.out.println("4. Delete Student");
        System.out.println("5. Display All Students");
        System.out.println("6. Generate Reports");
        System.out.println("7. Save Data");
        System.out.println("8. Load Data");
        System.out.println("0. Exit");
        System.out.println("========================================");
    }

    private static void addStudent() {
        System.out.println("\n=== ADD STUDENT ===");

        System.out.print("Enter Student ID: ");
        String studentID = scan.nextLine();

        System.out.print("Enter Name: ");
        String name = scan.nextLine();

        System.out.print("Enter Age: ");
        int age = scanInt.nextInt();

        System.out.print("Enter Course: ");
        String course = scan.nextLine();

        System.out.print("Enter Grade: ");
        double grade = scanInt.nextDouble();

        System.out.print("Enter Phone No: ");
        String phoneNo = scan.nextLine();

        System.out.print("Enter Address: ");
        String address = scan.nextLine();

        System.out.print("Select Student Type (1-Regular, 2-Undergraduate, 3-Graduate): ");
        int type = scanInt.nextInt();

        Student student;

        switch (type) {
            case 1:
                student = new Student(studentID, name, age, course, grade, phoneNo, address);
                break;
            case 2:
                System.out.print("Enter Major: ");
                String major = scan.nextLine();
                System.out.print("Enter Year Level: ");
                int yearLevel = scanInt.nextInt();
                student = new UndergraduateStudent(studentID, name, age, course, grade,
                        phoneNo, address, major, yearLevel);
                break;
            case 3:
                System.out.print("Enter Research Area: ");
                String researchArea = scan.nextLine();
                System.out.print("Enter Supervisor: ");
                String supervisor = scan.nextLine();
                student = new GraduateStudent(studentID, name, age, course, grade,
                        phoneNo, address, researchArea, supervisor);
                break;
            default:
                System.out.println("Invalid type! Creating regular student.");
                student = new Student(studentID, name, age, course, grade, phoneNo, address);
        }

        studentManager.addStudent(student);
    }

    private static void searchStudent() {
        System.out.println("\n=== SEARCH STUDENT ===");
        System.out.println("1. Search by ID");
        System.out.println("2. Search by Name");
        System.out.println("3. Search by Course");
        System.out.print("Enter your choice: ");
        int choice = scanInt.nextInt();

        switch (choice) {
            case 1:
                System.out.print("Enter Student ID: ");
                String studentID = scan.nextLine();
                Student student = studentManager.searchByID(studentID);
                if (student != null) {
                    System.out.println("\nStudent found:");
                    student.displayStudent();
                } else {
                    System.out.println("Student not found!");
                }
                break;
            case 2:
                System.out.print("Enter Name (or part of name): ");
                String name = scan.nextLine();
                Student[] nameResults = studentManager.searchByName(name);
                if (nameResults != null) {
                    System.out.println("\nStudents found:");
                    for (int i = 0; i < nameResults.length; i++) {
                        System.out.println("\nResult " + (i + 1) + ":");
                        nameResults[i].displayStudent();
                    }
                } else {
                    System.out.println("No students found with that name!");
                }
                break;
            case 3:
                System.out.print("Enter Course: ");
                String course = scan.nextLine();
                Student[] courseResults = studentManager.searchByCourse(course);
                if (courseResults != null) {
                    System.out.println("\nStudents found in " + course + ":");
                    for (int i = 0; i < courseResults.length; i++) {
                        System.out.println("\nResult " + (i + 1) + ":");
                        courseResults[i].displayStudent();
                    }
                } else {
                    System.out.println("No students found in that course!");
                }
                break;
            default:
                System.out.println("Invalid choice!");
        }
    }

    private static void updateStudent() {
        System.out.println("\n=== UPDATE STUDENT ===");
        System.out.print("Enter Student ID to update: ");
        String studentID = scan.nextLine();

        Student existingStudent = studentManager.searchByID(studentID);
        if (existingStudent == null) {
            System.out.println("Student not found!");
            return;
        }

        System.out.println("\nCurrent Student Information:");
        existingStudent.displayStudent();

        System.out.println("\nEnter new information (press Enter to keep current value):");

        System.out.print("Enter Name [" + existingStudent.getName() + "]: ");
        String name = scan.nextLine();
        if (name.trim().isEmpty()) {
            name = existingStudent.getName();
        }

        System.out.print("Enter Age [" + existingStudent.getAge() + "]: ");
        String ageStr = scan.nextLine();
        int age = ageStr.trim().isEmpty() ? existingStudent.getAge() : Integer.parseInt(ageStr);

        System.out.print("Enter Course [" + existingStudent.getCourse() + "]: ");
        String course = scan.nextLine();
        if (course.trim().isEmpty()) {
            course = existingStudent.getCourse();
        }

        System.out.print("Enter Grade [" + existingStudent.getGrade() + "]: ");
        String gradeStr = scan.nextLine();
        double grade = gradeStr.trim().isEmpty() ? existingStudent.getGrade() : Double.parseDouble(gradeStr);

        System.out.print("Enter Phone No [" + existingStudent.getPhoneNo() + "]: ");
        String phoneNo = scan.nextLine();
        if (phoneNo.trim().isEmpty()) {
            phoneNo = existingStudent.getPhoneNo();
        }

        System.out.print("Enter Address [" + existingStudent.getAddress() + "]: ");
        String address = scan.nextLine();
        if (address.trim().isEmpty()) {
            address = existingStudent.getAddress();
        }

        Student updatedStudent;

        if (existingStudent instanceof UndergraduateStudent) {
            UndergraduateStudent us = (UndergraduateStudent) existingStudent;
            System.out.print("Enter Major [" + us.getMajor() + "]: ");
            String major = scan.nextLine();
            if (major.trim().isEmpty()) {
                major = us.getMajor();
            }

            System.out.print("Enter Year Level [" + us.getYearLevel() + "]: ");
            String yearStr = scan.nextLine();
            int yearLevel = yearStr.trim().isEmpty() ? us.getYearLevel() : Integer.parseInt(yearStr);

            updatedStudent = new UndergraduateStudent(studentID, name, age, course, grade,
                    phoneNo, address, major, yearLevel);
        } else if (existingStudent instanceof GraduateStudent) {
            GraduateStudent gs = (GraduateStudent) existingStudent;
            System.out.print("Enter Research Area [" + gs.getResearchArea() + "]: ");
            String researchArea = scan.nextLine();
            if (researchArea.trim().isEmpty()) {
                researchArea = gs.getResearchArea();
            }

            System.out.print("Enter Supervisor [" + gs.getSupervisor() + "]: ");
            String supervisor = scan.nextLine();
            if (supervisor.trim().isEmpty()) {
                supervisor = gs.getSupervisor();
            }

            updatedStudent = new GraduateStudent(studentID, name, age, course, grade,
                    phoneNo, address, researchArea, supervisor);
        } else {
            updatedStudent = new Student(studentID, name, age, course, grade, phoneNo, address);
        }

        studentManager.updateStudent(studentID, updatedStudent);
    }

    private static void deleteStudent() {
        System.out.println("\n=== DELETE STUDENT ===");
        System.out.print("Enter Student ID to delete: ");
        String studentID = scan.nextLine();

        Student student = studentManager.searchByID(studentID);
        if (student == null) {
            System.out.println("Student not found!");
            return;
        }

        System.out.println("\nStudent to be deleted:");
        student.displayStudent();

        System.out.print("Are you sure you want to delete this student? (Y/N): ");
        char confirm = scan.nextLine().charAt(0);

        if (confirm == 'Y' || confirm == 'y') {
            studentManager.deleteStudent(studentID);
        } else {
            System.out.println("Delete operation cancelled.");
        }
    }

    private static void displayAllStudents() {
        System.out.println("\n=== ALL STUDENTS ===");
        studentManager.displayAllStudents();
        System.out.println("\nTotal Students: " + studentManager.getStudentCount());
    }

    private static void generateReports() {
        System.out.println("\n=== GENERATE REPORTS ===");
        System.out.println("1. Student List by Course");
        System.out.println("2. Performance Report");
        System.out.println("3. Enrollment Statistics");
        System.out.println("4. Individual Transcript");
        System.out.print("Enter your choice: ");
        int choice = scanInt.nextInt();

        switch (choice) {
            case 1:
                reportGenerator.generateStudentListByCourse();
                break;
            case 2:
                reportGenerator.generatePerformanceReport();
                break;
            case 3:
                reportGenerator.generateEnrollmentStatistics();
                break;
            case 4:
                System.out.print("Enter Student ID for transcript: ");
                String studentID = scan.nextLine();
                reportGenerator.generateTranscript(studentID);
                break;
            default:
                System.out.println("Invalid choice!");
        }
    }

    private static void saveData() {
        System.out.println("\n=== SAVE DATA ===");
        studentManager.saveToFile(DATA_FILE);
    }

    private static void loadData() {
        System.out.println("\n=== LOAD DATA ===");
        System.out.print("Enter filename to load from (or press Enter for default): ");
        String filename = scan.nextLine();
        if (filename.trim().isEmpty()) {
            filename = DATA_FILE;
        }
        studentManager.loadFromFile(filename);
    }
}