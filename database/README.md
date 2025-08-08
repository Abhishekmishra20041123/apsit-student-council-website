# EER Diagram Generation Instructions

## Option 1: Using MySQL Workbench
1. Install MySQL Workbench
2. Open MySQL Workbench
3. Go to File -> New Model
4. Go to File -> Import -> Reverse Engineer MySQL Create Script
5. Select the `eer_diagram.sql` file
6. Follow the wizard to import the tables
7. Go to Model -> Create Diagram from Catalog Objects
8. Arrange the tables as shown in the diagram
9. Go to File -> Export -> Export as PDF

## Option 2: Using draw.io
1. Go to [draw.io](https://app.diagrams.net/)
2. Create a new diagram
3. Use the Entity Relationship shape library
4. Create tables as shown in the diagram
5. Add relationships between tables
6. Export as PDF

## Option 3: Using Lucidchart
1. Go to [Lucidchart](https://www.lucidchart.com/)
2. Create a new document
3. Use the Entity Relationship Diagram template
4. Create tables as shown in the diagram
5. Add relationships between tables
6. Export as PDF

## Table Relationships
1. User (1:N) relationships:
   - User to StudyMaterial
   - User to TaskPlanner
   - User to Timetable
   - User to WorkshopReg
   - User to GPA

2. Mentor (1:N) relationships:
   - Mentor to Mentorship

3. StudyGroup (1:N) relationships:
   - StudyGroup to GroupMember

4. Workshop (1:N) relationships:
   - Workshop to WorkshopReg

## Notes
- All tables have appropriate foreign key constraints
- Timestamps are automatically set using CURRENT_TIMESTAMP
- ENUM types are used for status fields
- Appropriate data types are used for all fields
- Indexes are created on primary and foreign keys 