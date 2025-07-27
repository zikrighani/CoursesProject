public class GraduateStudent extends Student {
    private String researchArea;
    private String supervisor;

    public GraduateStudent() {
        super();
        researchArea = "";
        supervisor = "";
    }

    public GraduateStudent(String studentID, String name, int age, String course,
                           double grade, String phoneNo, String address,
                           String researchArea, String supervisor) {
        super(studentID, name, age, course, grade, phoneNo, address);
        this.researchArea = researchArea;
        this.supervisor = supervisor;
    }

    public String getResearchArea() {
        return researchArea;
    }

    public void setResearchArea(String researchArea) {
        this.researchArea = researchArea;
    }

    public String getSupervisor() {
        return supervisor;
    }

    public void setSupervisor(String supervisor) {
        this.supervisor = supervisor;
    }

    @Override
    public void displayStudent() {
        super.displayStudent();
        System.out.println("Research Area: " + researchArea);
        System.out.println("Supervisor: " + supervisor);
        System.out.println("Student Type: Graduate");
        System.out.println("-----------------------------------");
    }
}