#include <stdio.h>    
#include <string.h>  
#include <stdlib.h>  

#ifdef _WIN32
#include <windows.h>
#endif

#define MAX_STUDENTS 100         
#define MAX_ID_LENGTH 20        
#define MAX_NAME_LENGTH 50       
#define MAX_COURSE_LENGTH 50    
#define MAX_ADDRESS_LENGTH 100  
#define MAX_MAJOR_LENGTH 50      
#define MAX_RESEARCH_LENGTH 100  
#define FILENAME "students.txt"  

typedef enum {
    REGULAR,
    UNDERGRADUATE,
    GRADUATE
} StudentType;

typedef struct {
    char studentID[MAX_ID_LENGTH];
    char name[MAX_NAME_LENGTH];
    int age;
    float grade;
    char course[MAX_COURSE_LENGTH];
    char address[MAX_ADDRESS_LENGTH];

    StudentType type;
    union {
        struct {
            char major[MAX_MAJOR_LENGTH];
            int yearLevel;
        } undergraduate;
        struct {
            char researchArea[MAX_RESEARCH_LENGTH];
        } graduate;
    } typeDetails;
} Student;

Student students[MAX_STUDENTS];
int studentCount = 0;

void displayMenu();
void addStudent();
void updateStudent();
void deleteStudent();
void generateReports();
void saveStudentsToFile();
void loadStudentsFromFile();
void clearInputBuffer();
void printStudentType(StudentType type);

int searchStudentByID(const char* id);
int searchStudentByName(const char* name);
void displayStudent(const Student* s);

int main() {
    loadStudentsFromFile();

    int choice;
    do {
        displayMenu();
        printf("Enter your choice: ");
        if (scanf("%d", &choice) != 1) {
            printf("Invalid input. Please enter a number.\n");
            clearInputBuffer();
            continue;
        }
        clearInputBuffer();

        switch (choice) {
            case 1:
                addStudent();
                break;
            case 2:
                updateStudent();
                break;
            case 3:
                deleteStudent();
                break;
            case 4:
                {
                    char searchKey[MAX_NAME_LENGTH];
                    int searchChoice;
                    printf("Search by (1) ID or (2) Name? ");
                    if (scanf("%d", &searchChoice) != 1) {
                        printf("Invalid input. Try again.\n");
                        clearInputBuffer();
                        break;
                    }
                    clearInputBuffer();

                    int index = -1;
                    if (searchChoice == 1) {
                        printf("Enter Student ID to search: ");
                        fgets(searchKey, MAX_ID_LENGTH, stdin);
                        searchKey[strcspn(searchKey, "\n")] = 0;
                        index = searchStudentByID(searchKey);
                    } else if (searchChoice == 2) {
                        printf("Enter Student Name to search: ");
                        fgets(searchKey, MAX_NAME_LENGTH, stdin);
                        searchKey[strcspn(searchKey, "\n")] = 0;
                        index = searchStudentByName(searchKey);
                    } else {
                        printf("Invalid search choice.\n");
                    }

                    if (index != -1) {
                        printf("\n--- Student Found ---\n");
                        displayStudent(&students[index]);
                    } else {
                        printf("Student not found.\n");
                    }
                }
                break;
            case 5:
                generateReports();
                break;
            case 0:
                printf("Exiting Student Management System. Goodbye!\n");
                break;
            default:
                printf("Invalid choice. Please try again.\n");
        }
        printf("\nPress Enter to continue...");
        getchar();
        #ifdef _WIN32
        system("cls");
        #else
        system("clear");
        #endif

    } while (choice != 0);

    saveStudentsToFile();

    return 0;
}

void clearInputBuffer() {
    int c;
    while ((c = getchar()) != '\n' && c != EOF);
}

void displayMenu() {
    printf("--- Student Management System (C - Imperative) ---\n");
    printf("1. Add New Student\n");
    printf("2. Update Student Details\n");
    printf("3. Delete Student Record\n");
    printf("4. Search Student\n");
    printf("5. Generate Reports\n");
    printf("0. Exit\n");
    printf("-----------------------------------\n");
}

void displayStudent(const Student* s) {
    printf("  ID: %s\n", s->studentID);
    printf("  Name: %s\n", s->name);
    printf("  Age: %d\n", s->age);
    printf("  Grade: %.2f\n", s->grade);
    printf("  Course: %s\n", s->course);
    printf("  Address: %s\n", s->address);
    printf("  Student Type: ");
    printStudentType(s->type);

    if (s->type == UNDERGRADUATE) {
        printf("  Major: %s\n", s->typeDetails.undergraduate.major);
        printf("  Year Level: %d\n", s->typeDetails.undergraduate.yearLevel);
    } else if (s->type == GRADUATE) {
        printf("  Research Area: %s\n", s->typeDetails.graduate.researchArea);
    }
}

void printStudentType(StudentType type) {
    switch (type) {
        case REGULAR:
            printf("Regular\n");
            break;
        case UNDERGRADUATE:
            printf("Undergraduate\n");
            break;
        case GRADUATE:
            printf("Graduate\n");
            break;
        default:
            printf("Unknown\n");
            break;
    }
}


void addStudent() {
    if (studentCount >= MAX_STUDENTS) {
        printf("Error: Maximum number of students reached. Cannot add more.\n");
        return;
    }

    Student newStudent;
    printf("\n--- Add New Student ---\n");

    printf("Enter Student ID: ");
    fgets(newStudent.studentID, MAX_ID_LENGTH, stdin);
    newStudent.studentID[strcspn(newStudent.studentID, "\n")] = 0;

    if (searchStudentByID(newStudent.studentID) != -1) {
        printf("Error: Student with ID '%s' already exists. Cannot add duplicate.\n", newStudent.studentID);
        return;
    }

    printf("Enter Name: ");
    fgets(newStudent.name, MAX_NAME_LENGTH, stdin);
    newStudent.name[strcspn(newStudent.name, "\n")] = 0;

    printf("Enter Age: ");
    while (scanf("%d", &newStudent.age) != 1 || newStudent.age <= 0) {
        printf("Invalid age. Please enter a positive number: ");
        clearInputBuffer();
    }
    clearInputBuffer();

    printf("Enter Grade (e.g., 95.5): ");
    while (scanf("%f", &newStudent.grade) != 1 || newStudent.grade < 0 || newStudent.grade > 100) {
        printf("Invalid grade. Please enter a number between 0 and 100: ");
        clearInputBuffer();
    }
    clearInputBuffer();

    printf("Enter Course: ");
    fgets(newStudent.course, MAX_COURSE_LENGTH, stdin);
    newStudent.course[strcspn(newStudent.course, "\n")] = 0;

    printf("Enter Address: ");
    fgets(newStudent.address, MAX_ADDRESS_LENGTH, stdin);
    newStudent.address[strcspn(newStudent.address, "\n")] = 0;

    int typeChoice;
    printf("Select Student Type:\n");
    printf("1. Regular\n");
    printf("2. Undergraduate\n");
    printf("3. Graduate\n");
    printf("Enter choice: ");
    while (scanf("%d", &typeChoice) != 1 || typeChoice < 1 || typeChoice > 3) {
        printf("Invalid choice. Please enter 1, 2, or 3: ");
        clearInputBuffer();
    }
    clearInputBuffer();

    newStudent.type = (StudentType)(typeChoice - 1);

    if (newStudent.type == UNDERGRADUATE) {
        printf("Enter Major: ");
        fgets(newStudent.typeDetails.undergraduate.major, MAX_MAJOR_LENGTH, stdin);
        newStudent.typeDetails.undergraduate.major[strcspn(newStudent.typeDetails.undergraduate.major, "\n")] = 0;

        printf("Enter Year Level: ");
        while (scanf("%d", &newStudent.typeDetails.undergraduate.yearLevel) != 1 || newStudent.typeDetails.undergraduate.yearLevel <= 0) {
            printf("Invalid year level. Please enter a positive number: ");
            clearInputBuffer();
        }
        clearInputBuffer();
    } else if (newStudent.type == GRADUATE) {
        printf("Enter Research Area: ");
        fgets(newStudent.typeDetails.graduate.researchArea, MAX_RESEARCH_LENGTH, stdin);
        newStudent.typeDetails.graduate.researchArea[strcspn(newStudent.typeDetails.graduate.researchArea, "\n")] = 0;
    }

    students[studentCount] = newStudent;
    studentCount++;
    printf("Student '%s' added successfully.\n", newStudent.name);
}

int searchStudentByID(const char* id) {
    for (int i = 0; i < studentCount; i++) {
        if (strcmp(students[i].studentID, id) == 0) {
            return i;
        }
    }
    return -1;
}

int searchStudentByName(const char* name) {
    for (int i = 0; i < studentCount; i++) {
        #ifdef _WIN32
        if (_stricmp(students[i].name, name) == 0) {
            return i;
        }
        #else
        if (strcasecmp(students[i].name, name) == 0) {
            return i;
        }
        #endif
    }
    return -1;
}

void updateStudent() {
    char searchKey[MAX_NAME_LENGTH];
    int searchChoice;
    int index = -1;

    printf("\n--- Update Student Details ---\n");
    printf("Search by (1) ID or (2) Name? ");
    if (scanf("%d", &searchChoice) != 1) {
        printf("Invalid input. Try again.\n");
        clearInputBuffer();
        return;
    }
    clearInputBuffer();

    if (searchChoice == 1) {
        printf("Enter Student ID to update: ");
        fgets(searchKey, MAX_ID_LENGTH, stdin);
        searchKey[strcspn(searchKey, "\n")] = 0;
        index = searchStudentByID(searchKey);
    } else if (searchChoice == 2) {
        printf("Enter Student Name to update: ");
        fgets(searchKey, MAX_NAME_LENGTH, stdin);
        searchKey[strcspn(searchKey, "\n")] = 0;
        index = searchStudentByName(searchKey);
    } else {
        printf("Invalid search choice.\n");
        return;
    }

    if (index == -1) {
        printf("Student not found.\n");
        return;
    }

    printf("\nStudent found. Current details:\n");
    displayStudent(&students[index]);

    printf("\nEnter new details (leave blank and press Enter to keep current value):\n");

    char inputBuffer[MAX_RESEARCH_LENGTH];

    printf("New Name (%s): ", students[index].name);
    fgets(inputBuffer, MAX_NAME_LENGTH, stdin);
    inputBuffer[strcspn(inputBuffer, "\n")] = 0;
    if (strlen(inputBuffer) > 0) {
        strcpy(students[index].name, inputBuffer);
    }

    printf("New Age (%d): ", students[index].age);
    fgets(inputBuffer, sizeof(inputBuffer), stdin);
    inputBuffer[strcspn(inputBuffer, "\n")] = 0;
    if (strlen(inputBuffer) > 0) {
        int newAge;
        if (sscanf(inputBuffer, "%d", &newAge) == 1 && newAge > 0) {
            students[index].age = newAge;
        } else {
            printf("Invalid age. Keeping current age.\n");
        }
    }

    printf("New Grade (%.2f): ", students[index].grade);
    fgets(inputBuffer, sizeof(inputBuffer), stdin);
    inputBuffer[strcspn(inputBuffer, "\n")] = 0;
    if (strlen(inputBuffer) > 0) {
        float newGrade;
        if (sscanf(inputBuffer, "%f", &newGrade) == 1 && newGrade >= 0 && newGrade <= 100) {
            students[index].grade = newGrade;
        } else {
            printf("Invalid grade. Keeping current grade.\n");
        }
    }

    printf("New Course (%s): ", students[index].course);
    fgets(inputBuffer, MAX_COURSE_LENGTH, stdin);
    inputBuffer[strcspn(inputBuffer, "\n")] = 0;
    if (strlen(inputBuffer) > 0) {
        strcpy(students[index].course, inputBuffer);
    }

    printf("New Address (%s): ", students[index].address);
    fgets(inputBuffer, MAX_ADDRESS_LENGTH, stdin);
    inputBuffer[strcspn(inputBuffer, "\n")] = 0;
    if (strlen(inputBuffer) > 0) {
        strcpy(students[index].address, inputBuffer);
    }

    if (students[index].type == UNDERGRADUATE) {
        printf("New Major (%s): ", students[index].typeDetails.undergraduate.major);
        fgets(inputBuffer, MAX_MAJOR_LENGTH, stdin);
        inputBuffer[strcspn(inputBuffer, "\n")] = 0;
        if (strlen(inputBuffer) > 0) {
            strcpy(students[index].typeDetails.undergraduate.major, inputBuffer);
        }

        printf("New Year Level (%d): ", students[index].typeDetails.undergraduate.yearLevel);
        fgets(inputBuffer, sizeof(inputBuffer), stdin);
        inputBuffer[strcspn(inputBuffer, "\n")] = 0;
        if (strlen(inputBuffer) > 0) {
            int newYearLevel;
            if (sscanf(inputBuffer, "%d", &newYearLevel) == 1 && newYearLevel > 0) {
                students[index].typeDetails.undergraduate.yearLevel = newYearLevel;
            } else {
                printf("Invalid year level. Keeping current year level.\n");
            }
        }
    } else if (students[index].type == GRADUATE) {
        printf("New Research Area (%s): ", students[index].typeDetails.graduate.researchArea);
        fgets(inputBuffer, MAX_RESEARCH_LENGTH, stdin);
        inputBuffer[strcspn(inputBuffer, "\n")] = 0;
        if (strlen(inputBuffer) > 0) {
            strcpy(students[index].typeDetails.graduate.researchArea, inputBuffer);
        }
    }

    printf("Student details updated successfully.\n");
}

void deleteStudent() {
    char searchKey[MAX_NAME_LENGTH];
    int searchChoice;
    int index = -1;

    printf("\n--- Delete Student Record ---\n");
    printf("Search by (1) ID or (2) Name? ");
    if (scanf("%d", &searchChoice) != 1) {
        printf("Invalid input. Try again.\n");
        clearInputBuffer();
        return;
    }
    clearInputBuffer();

    if (searchChoice == 1) {
        printf("Enter Student ID to delete: ");
        fgets(searchKey, MAX_ID_LENGTH, stdin);
        searchKey[strcspn(searchKey, "\n")] = 0;
        index = searchStudentByID(searchKey);
    } else if (searchChoice == 2) {
        printf("Enter Student Name to delete: ");
        fgets(searchKey, MAX_NAME_LENGTH, stdin);
        searchKey[strcspn(searchKey, "\n")] = 0;
        index = searchStudentByName(searchKey);
    } else {
        printf("Invalid search choice.\n");
        return;
    }

    if (index == -1) {
        printf("Student not found.\n");
        return;
    }

    printf("\nStudent found. Details:\n");
    displayStudent(&students[index]);

    char confirm;
    printf("Are you sure you want to delete this student? (y/n): ");
    scanf(" %c", &confirm);
    clearInputBuffer();

    if (confirm == 'y' || confirm == 'Y') {
        for (int i = index; i < studentCount - 1; i++) {
            students[i] = students[i + 1];
        }
        studentCount--;
        printf("Student record deleted successfully.\n");
    } else {
        printf("Deletion cancelled.\n");
    }
}

void saveStudentsToFile() {
    FILE* file = fopen(FILENAME, "w");
    if (file == NULL) {
        printf("Error: Could not open file '%s' for writing.\n", FILENAME);
        return;
    }

    for (int i = 0; i < studentCount; i++) {
        fprintf(file, "%s,%s,%d,%.2f,%s,%s,%d",
                students[i].studentID,
                students[i].name,
                students[i].age,
                students[i].grade,
                students[i].course,
                students[i].address,
                students[i].type);

        if (students[i].type == UNDERGRADUATE) {
            fprintf(file, ",%s,%d\n",
                    students[i].typeDetails.undergraduate.major,
                    students[i].typeDetails.undergraduate.yearLevel);
        } else if (students[i].type == GRADUATE) {
            fprintf(file, ",%s\n",
                    students[i].typeDetails.graduate.researchArea);
        } else {
            fprintf(file, "\n");
        }
    }

    fclose(file);
    printf("Student data saved to '%s' successfully.\n", FILENAME);
}

void loadStudentsFromFile() {
    FILE* file = fopen(FILENAME, "r");
    if (file == NULL) {
        printf("No existing student data file found. Starting with an empty system.\n");
        return;
    }

    studentCount = 0;
    char lineBuffer[512];

    while (studentCount < MAX_STUDENTS && fgets(lineBuffer, sizeof(lineBuffer), file) != NULL) {
        lineBuffer[strcspn(lineBuffer, "\n")] = 0;

        Student tempStudent;
        int typeInt;
        char *token;

        token = strtok(lineBuffer, ",");
        if (token) strcpy(tempStudent.studentID, token); else continue;
        token = strtok(NULL, ",");
        if (token) strcpy(tempStudent.name, token); else continue;
        token = strtok(NULL, ",");
        if (token) tempStudent.age = atoi(token); else continue;
        token = strtok(NULL, ",");
        if (token) tempStudent.grade = atof(token); else continue;
        token = strtok(NULL, ",");
        if (token) strcpy(tempStudent.course, token); else continue;
        token = strtok(NULL, ",");
        if (token) strcpy(tempStudent.address, token); else continue;
        token = strtok(NULL, ",");
        if (token) typeInt = atoi(token); else continue;

        tempStudent.type = (StudentType)typeInt;

        if (tempStudent.type == UNDERGRADUATE) {
            token = strtok(NULL, ",");
            if (token) strcpy(tempStudent.typeDetails.undergraduate.major, token); else continue;
            token = strtok(NULL, ",");
            if (token) tempStudent.typeDetails.undergraduate.yearLevel = atoi(token); else continue;
        } else if (tempStudent.type == GRADUATE) {
            token = strtok(NULL, ",");
            if (token) strcpy(tempStudent.typeDetails.graduate.researchArea, token); else continue;
        }

        students[studentCount] = tempStudent;
        studentCount++;
    }

    fclose(file);
    printf("Loaded %d student records from '%s'.\n", studentCount, FILENAME);
}

void generateReports() {
    int reportChoice;
    printf("\n--- Generate Reports ---\n");
    printf("1. Display All Students (Student List)\n");
    printf("2. Grade Report\n");
    printf("3. Enrollment Statistics\n");
    printf("4. Students by Type\n");
    printf("Enter your report choice: ");
    if (scanf("%d", &reportChoice) != 1) {
        printf("Invalid input. Try again.\n");
        clearInputBuffer();
        return;
    }
    clearInputBuffer();

    switch (reportChoice) {
        case 1:
            printf("\n--- All Students List ---\n");
            if (studentCount == 0) {
                printf("No students to display.\n");
            } else {
                for (int i = 0; i < studentCount; i++) {
                    printf("Student #%d:\n", i + 1);
                    displayStudent(&students[i]);
                    printf("-----------------------------------\n");
                }
            }
            break;
        case 2:
            printf("\n--- Grade Report ---\n");
            if (studentCount == 0) {
                printf("No students to generate grade report for.\n");
            } else {
                float totalGrade = 0;
                int studentsBelow70 = 0;

                printf("Individual Student Grades:\n");
                for (int i = 0; i < studentCount; i++) {
                    printf("  %s (%s): %.2f\n", students[i].name, students[i].studentID, students[i].grade);
                    totalGrade += students[i].grade;
                    if (students[i].grade < 70.0) {
                        studentsBelow70++;
                    }
                }
                printf("\n--- Summary ---\n");
                printf("Total Students: %d\n", studentCount);
                if (studentCount > 0) {
                    printf("Average Grade: %.2f\n", totalGrade / studentCount);
                }
                printf("Students with Grade Below 70: %d\n", studentsBelow70);
            }
            break;
        case 3:
            printf("\n--- Enrollment Statistics ---\n");
            if (studentCount == 0) {
                printf("No enrollment data available.\n");
            } else {
                printf("Total Enrolled Students: %d\n", studentCount);
                printf("\nStudents by Course:\n");
                for(int i = 0; i < studentCount; i++) {
                    printf("  Course: %-20s Student: %s (%s)\n", students[i].course, students[i].name, students[i].studentID);
                }
            }
            break;
        case 4:
            printf("\n--- Students by Type Report ---\n");
            if (studentCount == 0) {
                printf("No students to display by type.\n");
            } else {
                int regularCount = 0;
                int undergraduateCount = 0;
                int graduateCount = 0;

                printf("\n--- Regular Students ---\n");
                for (int i = 0; i < studentCount; i++) {
                    if (students[i].type == REGULAR) {
                        displayStudent(&students[i]);
                        printf("-----------------------------------\n");
                        regularCount++;
                    }
                }
                if (regularCount == 0) printf("No regular students found.\n");

                printf("\n--- Undergraduate Students ---\n");
                for (int i = 0; i < studentCount; i++) {
                    if (students[i].type == UNDERGRADUATE) {
                        displayStudent(&students[i]);
                        printf("-----------------------------------\n");
                        undergraduateCount++;
                    }
                }
                if (undergraduateCount == 0) printf("No undergraduate students found.\n");

                printf("\n--- Graduate Students ---\n");
                for (int i = 0; i < studentCount; i++) {
                    if (students[i].type == GRADUATE) {
                        displayStudent(&students[i]);
                        printf("-----------------------------------\n");
                        graduateCount++;
                    }
                }
                if (graduateCount == 0) printf("No graduate students found.\n");

                printf("\n--- Type Summary ---\n");
                printf("Regular Students: %d\n", regularCount);
                printf("Undergraduate Students: %d\n", undergraduateCount);
                printf("Graduate Students: %d\n", graduateCount);
                printf("Total Students: %d\n", studentCount);
            }
            break;
        default:
            printf("Invalid report choice.\n");
            break;
    }
}
