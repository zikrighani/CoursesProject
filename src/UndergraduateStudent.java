public class UndergraduateStudent extends Student {
    private String major;
    private int yearLevel;

    public UndergraduateStudent() {
        super();
        major = "";
        yearLevel = 1;
    }

    public UndergraduateStudent(String studentID, String name, int age, String course,
                                double grade, String phoneNo, String address,
                                String major, int yearLevel) {
        super(studentID, name, age, course, grade, phoneNo, address);
        this.major = major;
        this.yearLevel = yearLevel;
    }

    public String getMajor() {
        return major;
    }

    public void setMajor(String major) {
        this.major = major;
    }

    public int getYearLevel() {
        return yearLevel;
    }

    public void setYearLevel(int yearLevel) {
        this.yearLevel = yearLevel;
    }

    @Override
    public void displayStudent() {
        super.displayStudent();
        System.out.println("Major: " + major);
        System.out.println("Year Level: " + yearLevel);
        System.out.println("Student Type: Undergraduate");
        System.out.println("-----------------------------------");
    }
}