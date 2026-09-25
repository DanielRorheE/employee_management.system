-- Employee Management System database schema
-- MySQL/MariaDB compatible
-- Includes both modern ERP-style fields and legacy UI fields to keep the app working.

DROP DATABASE IF EXISTS employee_system;
CREATE DATABASE employee_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE employee_system;

CREATE TABLE departments (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_departments_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE positions (
    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    salary_range_min DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    salary_range_max DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    department_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_positions_title_department (title, department_id),
    KEY idx_positions_department (department_id),
    CONSTRAINT fk_positions_department
        FOREIGN KEY (department_id) REFERENCES departments(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT chk_positions_salary_range
        CHECK (salary_range_min >= 0 AND salary_range_max >= salary_range_min)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE employees (
    id INT NOT NULL AUTO_INCREMENT,
    employee_code VARCHAR(30) NULL,
    first_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NULL,
    full_name VARCHAR(200) NULL,
    position VARCHAR(100) NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NULL,
    department VARCHAR(100) NULL,
    hire_date DATE NULL,
    status ENUM('Active', 'Inactive', 'On Leave') NOT NULL DEFAULT 'Active',
    department_id INT NULL,
    position_id INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_employees_employee_code (employee_code),
    UNIQUE KEY uq_employees_email (email),
    KEY idx_employees_department (department_id),
    KEY idx_employees_position (position_id),
    KEY idx_employees_status (status),
    KEY idx_employees_deleted_at (deleted_at),
    CONSTRAINT fk_employees_department
        FOREIGN KEY (department_id) REFERENCES departments(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_employees_position
        FOREIGN KEY (position_id) REFERENCES positions(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attendance (
    id INT NOT NULL AUTO_INCREMENT,
    employee_id INT NOT NULL,
    clock_in DATETIME NOT NULL,
    clock_out DATETIME NULL,
    total_hours DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    status ENUM('Present', 'Late', 'Absent', 'Half-Day') NOT NULL DEFAULT 'Present',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_attendance_employee (employee_id),
    KEY idx_attendance_status (status),
    CONSTRAINT fk_attendance_employee
        FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT chk_attendance_hours
        CHECK (total_hours >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE leaves (
    id INT NOT NULL AUTO_INCREMENT,
    employee_id INT NOT NULL,
    leave_type ENUM('Sick', 'Vacation', 'Casual', 'Unpaid') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_leaves_employee (employee_id),
    KEY idx_leaves_status (status),
    KEY idx_leaves_dates (start_date, end_date),
    CONSTRAINT fk_leaves_employee
        FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT chk_leave_dates
        CHECK (end_date >= start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payroll (
    id INT NOT NULL AUTO_INCREMENT,
    employee_id INT NOT NULL,
    pay_period_start DATE NOT NULL,
    pay_period_end DATE NOT NULL,
    base_salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    allowances DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    deductions DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    net_pay DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('Draft', 'Processed', 'Paid') NOT NULL DEFAULT 'Draft',
    payment_date DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_payroll_employee_period (employee_id, pay_period_start, pay_period_end),
    KEY idx_payroll_employee (employee_id),
    KEY idx_payroll_status (status),
    CONSTRAINT fk_payroll_employee
        FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT chk_payroll_period
        CHECK (pay_period_end >= pay_period_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO departments (name, description) VALUES
('Human Resources', 'People operations and employee support'),
('Information Technology', 'Technical systems and infrastructure'),
('Finance', 'Budgeting, accounting and payroll support'),
('Operations', 'Daily business operations and workflow management');

INSERT INTO positions (title, salary_range_min, salary_range_max, department_id) VALUES
('HR Manager', 50000.00, 80000.00, 1),
('Software Engineer', 70000.00, 120000.00, 2),
('Accountant', 45000.00, 75000.00, 3),
('Operations Manager', 60000.00, 95000.00, 4);

INSERT INTO employees (
    employee_code,
    first_name,
    last_name,
    full_name,
    position,
    email,
    phone,
    department,
    hire_date,
    status,
    department_id,
    position_id,
    created_at
) VALUES (
    'EMP00001',
    'Jul',
    'Khair',
    'Jul Khair',
    'Skibidi',
    'Julthegreat@gmail.com',
    '+123456789',
    'Finance',
    '2026-09-01',
    'Active',
    3,
    3,
    NOW()
);
