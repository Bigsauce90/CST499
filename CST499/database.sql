create database if not exists course enrollment cst499;
use course enrollment cst499;

set foreign_key_checks = 0;

drop table if exists notifications;
drop table if exists waitlist;
drop table if exists enrollments;
drop table if exists offerings;
drop table if exists courses;
drop table if exists semesters;
drop table if exists students;

set foreign_key_checks = 1;

create table students (
    student_id int auto_increment primary key,
    first_name varchar(50) not null,
    last_name varchar(50) not null,
    email varchar(100) not null unique,
    phone_number varchar(20),
    program varchar(100) not null
);
create table semesters (
    semester_id int auto_increment primary key,
    semester_name varchar(100) not null,
    year int not null
);
create table courses (
    course_id int auto_increment primary key,
    course_name varchar(100) not null,
    course_code varchar(20) not null unique
);
create table offerings (
    id int auto_increment primary key,
    course_id int not null,
    semester_id int not null,
    max_capacity int not null,
    foreign key (course_id) references courses(course_id),
    foreign key (semester_id) references semesters(semester_id)
);
create table enrollments (
    id int auto_increment primary key,
    student_id int not null,
    offering_id int not null,
    unique (student_id, offering_id),
    foreign key (student_id) references students(student_id),
    foreign key (offering_id) references offerings(id)
);
create table waitlist (
    id int auto_increment primary key,
    student_id int not null,
    offering_id int not null,
    status varchar(20) not null default 'pending',
    joined_at timestamp default current_timestamp,
    unique (student_id, offering_id),
    foreign key (student_id) references students(student_id),
    foreign key (offering_id) references offerings(id)
);
create table notifications (
    id int auto_increment primary key,
    student_id int not null,
    offering_id int not null,
    message text not null,
    read_status varchar(20) not null default 'unread',
    created_at timestamp default current_timestamp,
    foreign key (student_id) references students(student_id),
    foreign key (offering_id) references offerings(id)
);
insert into semesters (semester_name, year) values
('spring', 2026),
('summer', 2026),
('fall', 2026);

insert into courses (course_name, course_code) values
('writing', 'write101'),
('math', 'math101'),
('reading', 'read101'),
('science', 'science101'),
('history', 'history101'),
('art', 'art101');

insert into offerings (course_id, semester_id, max_capacity) values
(1, 1, 10),
(2, 1, 10),
(3, 2, 10),
(4, 2, 10),
(5, 3, 10),
(6, 3, 10);



