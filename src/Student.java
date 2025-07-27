public class Student {
    private String studentID;
    private String name;
    private int age;
    private String course;
    private double grade;
    private String phoneNo;
    private String address;

    // Default constructor
    public Student() {
        studentID = "";
        name = "";
        age = 0;
        course = "";
        grade = 0.0;
        phoneNo = "";
        address = "";
    }

    // Parameterized constructor
    public Student(String studentID, String name, int age, String course, double grade, String phoneNo, String address) {
        this.studentID = studentID;
        this.name = name;
        this.age = age;
        this.course = course;
        this.grade = grade;
        this.phoneNo = phoneNo;
        this.address = address;
    }

    // Getter methods
    public String getStudentID() {
        return studentID;
    }

    public String getName() {
        return name;
    }

    public int getAge() {
        return age;
    }

    public String getCourse() {
        return course;
    }

    public double getGrade() {
        return grade;
    }

    public String getPhoneNo() {
        return phoneNo;
    }

    public String getAddress() {
        return address;
    }

    // Setter methods
    public void setStudentID(String studentID) {
        this.studentID = studentID;
    }

    public void setName(String name) {
        this.name = name;
    }

    public void setAge(int age) {
        this.age = age;
    }

    public void setCourse(String course) {
        this.course = course;
    }

    public void setGrade(double grade) {
        this.grade = grade;
    }

    public void setPhoneNo(String phoneNo) {
        this.phoneNo = phoneNo;
    }

    public void setAddress(String address) {
        this.address = address;
    }

    // Method to display student information
    public void displayStudent() {
        System.out.println("Student ID: " + studentID);
        System.out.println("Name: " + name);
        System.out.println("Age: " + age);
        System.out.println("Course: " + course);
        System.out.println("Grade: " + String.format("%.2f", grade));
        System.out.println("Phone No: " + phoneNo);
        System.out.println("Address: " + address);
        System.out.println("-----------------------------------");
    }

    // Method to get grade letter
    public String getGradeLetter() {
        if (grade >= 90) return "A+";
        else if (grade >= 80) return "A";
        else if (grade >= 75) return "A-";
        else if (grade >= 70) return "B+";
        else if (grade >= 65) return "B";
        else if (grade >= 60) return "B-";
        else if (grade >= 55) return "C+";
        else if (grade >= 50) return "C";
        else if (grade >= 47) return "C-";
        else if (grade >= 44) return "D+";
        else if (grade >= 40) return "D ";
        else return "F";
    }
}