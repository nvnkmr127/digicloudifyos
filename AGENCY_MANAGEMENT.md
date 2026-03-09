# 🏢 Marketing Agency Management System

## Overview
Complete enterprise-grade agency management system with employee management, workload analysis, time tracking, project management, invoicing, and comprehensive analytics.

---

## 📦 New Modules Added

### 1. **Employee Management** 👥

#### Models Created
- [Employee.php](file:///Users/naveenadicharla/Documents/DC%20OS/laravel-app/app/Models/Employee.php)

#### Features
- **Complete employee profiles** with departments, positions, skills
- **Hierarchical structure** (managers and subordinates)
- **Employment types** (full-time, part-time, contractor)
- **Performance tracking** with ratings
- **Salary and hourly rate** management
- **Skills and certifications** tracking
- **Soft deletes** for data retention

#### Employee Fields
```php
- employee_code: Unique identifier
- department: Sales, Creative, Account Management, etc.
- position: Account Manager, Designer, Developer, etc.
- employment_type: Full-time, Part-time, Contractor
- join_date: Start date
- salary: Monthly/annual salary
- hourly_rate: For billing calculations
- skills: Array of skills
- certifications: Array of certifications
- status: Active, Inactive, On Leave
- manager_id: Reports to whom
- work_hours_per_week: 40, 30, etc.
- performance_rating: 1-5 rating
```

#### Key Methods
```php
$employee->getCurrentWorkload();     // Current allocated hours
$employee->getUtilizationRate();     // Percentage of capacity used
$employee->subordinates;             // Direct reports
$employee->projectAssignments;       // Assigned projects
```

---

### 2. **Workload Analysis System** 📊

#### Service Created
- [WorkloadAnalysisService.php](file:///Users/naveenadicharla/Documents/DC%20OS/laravel-app/app/Services/WorkloadAnalysisService.php)

#### Models
- [WorkloadEntry.php](file:///Users/naveenadicharla/Documents/DC%20OS/laravel-app/app/Models/WorkloadEntry.php)

#### API Endpoint
```bash
GET /api/v1/workload/analysis?period=current_week
```

#### Supported Periods
- `current_week` - This week
- `next_week` - Next week
- `current_month` - This month
- `next_month` - Next month
- `current_quarter` - Current quarter

#### Analysis Components

##### **Employee Workload**
```json
{
    "employee_id": "uuid",
    "name": "John Doe",
    "department": "Creative",
    "position": "Designer",
    "available_hours": 40,
    "allocated_hours": 35,
    "actual_hours": 32,
    "utilization_rate": 87.5,
    "is_overallocated": false,
    "capacity_status": "optimal",
    "active_projects": 3
}
```

**Capacity Status Levels:**
- `overallocated` - 100%+
- `at_capacity` - 85-99%
- `optimal` - 60-84%
- `under_utilized` - 40-59%
- `low_utilization` - <40%

##### **Project Workload**
```json
{
    "project_id": "uuid",
    "name": "Social Media Campaign",
    "client": "Acme Corp",
    "status": "active",
    "team_size": 5,
    "allocated_hours": 120,
    "actual_hours": 95,
    "budget_utilization": 78.5,
    "health_status": "on_track",
    "is_on_track": true
}
```

##### **Department Breakdown**
```json
{
    "department": "Creative",
    "employee_count": 8,
    "total_available_hours": 320,
    "total_allocated_hours": 275,
    "total_actual_hours": 260,
    "utilization_rate": 85.94
}
```

##### **Capacity Analysis**
```json
{
    "total_capacity": 1600,
    "total_allocated": 1350,
    "total_actual": 1280,
    "available_capacity": 250,
    "utilization_rate": 84.38,
    "efficiency_rate": 94.81
}
```

##### **Overallocation Warnings**
```json
[
    {
        "employee_id": "uuid",
        "name": "Jane Smith",
        "department": "Account Management",
        "available_hours": 40,
        "allocated_hours": 50,
        "overallocation_hours": 10,
        "overallocation_percentage": 25
    }
]
```

---

### 3. **Time Tracking Module** ⏱️

#### Model Created
- [TimeEntry.php](file:///Users/naveenadicharla/Documents/DC%20OS/laravel-app/app/Models/TimeEntry.php)

#### Features
- **Detailed time entries** per employee, project, and task
- **Billable/non-billable** tracking
- **Approval workflow** with approvers
- **Daily time logs** with descriptions
- **Project and task association**

#### Time Entry Fields
```php
- employee_id: Who logged the time
- project_id: Which project
- task_id: Specific task (optional)
- date: Date of work
- hours: Hours worked (decimal)
- description: What was done
- billable: true/false
- approved: true/false
- approved_by: Manager/admin who approved
- approved_at: Approval timestamp
```

#### Usage Example
```php
TimeEntry::create([
    'organization_id' => $orgId,
    'employee_id' => $employeeId,
    'project_id' => $projectId,
    'date' => '2024-03-08',
    'hours' => 5.5,
    'description' => 'Designed social media graphics',
    'billable' => true,
    'approved' => false,
]);

// Query examples
$billableHours = TimeEntry::billable()->approved()->sum('hours');
$weekHours = TimeEntry::forPeriod($startDate, $endDate)->sum('hours');
```

---

### 4. **Project Management** 📁

#### Model Created
- [Project.php](file:///Users/naveenadicharla/Documents/DC%20OS/laravel-app/app/Models/Project.php)
- [ProjectAssignment.php](file:///Users/naveenadicharla/Documents/DC%20OS/laravel-app/app/Models/ProjectAssignment.php)

#### Project Fields
```php
- client_id: Which client
- name: Project name
- project_code: Unique identifier
- status: Active, Completed, On Hold, Cancelled
- priority: High, Medium, Low
- start_date & end_date: Timeline
- budget: Total budget
- actual_cost: Running costs
- billing_type: Fixed, Hourly, Retainer
- hourly_rate: For hourly billing
- project_manager_id: Who manages it
- health_status: On Track, At Risk, Critical
```

#### Project Team Assignments
```php
ProjectAssignment::create([
    'project_id' => $projectId,
    'employee_id' => $employeeId,
    'role' => 'Lead Designer',
    'start_date' => '2024-03-01',
    'end_date' => '2024-04-30',
    'allocation_percentage' => 50,  // 50% of their time
    'hourly_rate' => 75.00,
    'is_active' => true,
]);
```

#### Project Analytics
```php
$project->getBudgetUtilization();      // % of budget used
$project->getTotalBillableHours();     // Total billable hours
$project->getProjectedRevenue();       // Expected revenue
$project->isProfitable();              // Is making money?
```

---

### 5. **Invoice & Billing System** 💰

#### Model Created
- [Invoice.php](file:///Users/naveenadicharla/Documents/DC%20OS/laravel-app/app/Models/Invoice.php)

#### Invoice Fields
```php
- client_id: Who to bill
- project_id: Related project
- invoice_number: INV-2024-001
- issue_date: Invoice date
- due_date: Payment due date
- status: Draft, Pending, Paid, Overdue, Cancelled
- subtotal: Pre-tax amount
- tax_amount: Tax
- discount_amount: Discounts
- total_amount: Final amount
- paid_amount: Amount received
- notes: Additional info
- payment_terms: Net 30, etc.
```

#### Invoice Methods
```php
$invoice->balance;                     // Remaining to pay
$invoice->isPaid();                    // Fully paid?
$invoice->isOverdue();                 // Past due date?
```

#### Billing Automation
```php
// Generate invoice from time entries
$timeEntries = TimeEntry::where('project_id', $projectId)
    ->billable()
    ->approved()
    ->whereNull('invoice_id')
    ->get();

$subtotal = $timeEntries->sum(function($entry) {
    return $entry->hours * $project->hourly_rate;
});

Invoice::create([
    'client_id' => $project->client_id,
    'project_id' => $project->id,
    'invoice_number' => 'INV-' . now()->format('Y-m') . '-' . $nextNumber,
    'issue_date' => now(),
    'due_date' => now()->addDays(30),
    'subtotal' => $subtotal,
    'tax_amount' => $subtotal * 0.1,  // 10% tax
    'total_amount' => $subtotal * 1.1,
    'status' => 'pending',
]);
```

---

### 6. **Agency Dashboard** 🎯

#### Service Created
- [AgencyManagementService.php](file:///Users/naveenadicharla/Documents/DC%20OS/laravel-app/app/Services/AgencyManagementService.php)
- [AgencyController.php](file:///Users/naveenadicharla/Documents/DC%20OS/laravel-app/app/Http/Controllers/Api/V1/AgencyController.php)

#### API Endpoint
```bash
GET /api/v1/agency/dashboard
```

#### Dashboard Sections

##### **1. Overview**
```json
{
    "active_projects": 12,
    "active_clients": 8,
    "team_size": 25,
    "pending_invoices": 5,
    "overdue_invoices": 2
}
```

##### **2. Financial Metrics**
```json
{
    "monthly_revenue": 45000.00,
    "monthly_pending": 12000.00,
    "yearly_revenue": 425000.00,
    "total_project_costs": 280000.00,
    "profit_margin": 34.12,
    "average_project_value": 15000.00
}
```

##### **3. Team Metrics**
```json
{
    "total_employees": 25,
    "by_department": {
        "Creative": 10,
        "Account Management": 8,
        "Development": 5,
        "Sales": 2
    },
    "total_hours_this_week": 980,
    "billable_hours_this_week": 750,
    "billable_ratio": 76.53,
    "average_utilization": 82.5
}
```

##### **4. Client Metrics**
```json
{
    "total_clients": 15,
    "active_clients": 12,
    "clients_with_active_projects": 10,
    "new_clients_this_month": 2,
    "client_retention_rate": 85.71
}
```

##### **5. Project Metrics**
```json
{
    "total_projects": 45,
    "active_projects": 12,
    "on_track": 9,
    "at_risk": 3,
    "average_project_duration": 60,
    "completion_rate": 73.33
}
```

##### **6. Productivity Metrics**
```json
{
    "total_hours_this_month": 4200,
    "billable_hours_this_month": 3200,
    "revenue_per_hour": 85.50,
    "billable_utilization": 76.19
}
```

---

## 🗄️ Database Schema

### Employees Table
```sql
CREATE TABLE employees (
    id UUID PRIMARY KEY,
    organization_id UUID NOT NULL,
    user_id UUID NOT NULL,
    employee_code VARCHAR(50) UNIQUE,
    department VARCHAR(100),
    position VARCHAR(100),
    employment_type VARCHAR(50),
    join_date DATE,
    salary DECIMAL(10,2),
    hourly_rate DECIMAL(10,2),
    skills JSON,
    certifications JSON,
    status VARCHAR(50),
    manager_id UUID,
    work_hours_per_week INT,
    performance_rating DECIMAL(3,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

### Time Entries Table
```sql
CREATE TABLE time_entries (
    id UUID PRIMARY KEY,
    organization_id UUID NOT NULL,
    employee_id UUID NOT NULL,
    project_id UUID,
    task_id UUID,
    date DATE NOT NULL,
    hours DECIMAL(5,2) NOT NULL,
    description TEXT,
    billable BOOLEAN DEFAULT true,
    approved BOOLEAN DEFAULT false,
    approved_by UUID,
    approved_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Workload Entries Table
```sql
CREATE TABLE workload_entries (
    id UUID PRIMARY KEY,
    organization_id UUID NOT NULL,
    employee_id UUID NOT NULL,
    project_id UUID NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    allocated_hours DECIMAL(6,2) NOT NULL,
    actual_hours DECIMAL(6,2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Projects Table
```sql
CREATE TABLE projects (
    id UUID PRIMARY KEY,
    organization_id UUID NOT NULL,
    client_id UUID NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    project_code VARCHAR(50) UNIQUE,
    status VARCHAR(50),
    priority VARCHAR(50),
    start_date DATE,
    end_date DATE,
    budget DECIMAL(12,2),
    actual_cost DECIMAL(12,2) DEFAULT 0,
    billing_type VARCHAR(50),
    hourly_rate DECIMAL(10,2),
    project_manager_id UUID,
    health_status VARCHAR(50),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

### Project Assignments Table
```sql
CREATE TABLE project_assignments (
    id UUID PRIMARY KEY,
    organization_id UUID NOT NULL,
    project_id UUID NOT NULL,
    employee_id UUID NOT NULL,
    role VARCHAR(100),
    start_date DATE,
    end_date DATE,
    allocation_percentage INT,
    hourly_rate DECIMAL(10,2),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Invoices Table
```sql
CREATE TABLE invoices (
    id UUID PRIMARY KEY,
    organization_id UUID NOT NULL,
    client_id UUID NOT NULL,
    project_id UUID,
    invoice_number VARCHAR(50) UNIQUE,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status VARCHAR(50),
    subtotal DECIMAL(12,2),
    tax_amount DECIMAL(12,2),
    discount_amount DECIMAL(12,2),
    total_amount DECIMAL(12,2),
    paid_amount DECIMAL(12,2) DEFAULT 0,
    notes TEXT,
    payment_terms VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🚀 Usage Examples

### 1. Create Employee
```php
$employee = Employee::create([
    'organization_id' => $orgId,
    'user_id' => $userId,
    'employee_code' => 'EMP-001',
    'department' => 'Creative',
    'position' => 'Senior Designer',
    'employment_type' => 'Full-time',
    'join_date' => '2024-01-15',
    'hourly_rate' => 75.00,
    'skills' => ['Photoshop', 'Illustrator', 'Figma'],
    'status' => 'active',
    'work_hours_per_week' => 40,
]);
```

### 2. Assign Employee to Project
```php
ProjectAssignment::create([
    'project_id' => $project->id,
    'employee_id' => $employee->id,
    'role' => 'Lead Designer',
    'allocation_percentage' => 60,
    'is_active' => true,
]);
```

### 3. Log Time Entry
```php
TimeEntry::create([
    'employee_id' => $employee->id,
    'project_id' => $project->id,
    'date' => today(),
    'hours' => 6.5,
    'description' => 'Created website mockups',
    'billable' => true,
]);
```

### 4. Check Team Workload
```php
$workload = app(WorkloadAnalysisService::class)
    ->getTeamWorkload($orgId, 'current_week');

// Find overallocated employees
$overallocated = collect($workload['overallocation_warnings']);
```

### 5. Get Agency Metrics
```php
$dashboard = app(AgencyManagementService::class)
    ->getAgencyDashboard($orgId);

$revenue = $dashboard['financial']['monthly_revenue'];
$utilization = $dashboard['team']['billable_ratio'];
```

---

## 📊 Key Performance Indicators (KPIs)

### Team KPIs
- **Utilization Rate**: % of available hours allocated
- **Billable Ratio**: % of hours that are billable
- **Average Performance Rating**: Team performance scores

### Project KPIs
- **Budget Utilization**: % of budget spent
- **On-Time Delivery**: % of projects delivered on schedule
- **Profitability**: Revenue vs. cost per project

### Financial KPIs
- **Revenue per Hour**: Average revenue generated per billable hour
- **Profit Margin**: (Revenue - Cost) / Revenue
- **Outstanding AR**: Unpaid invoices amount

### Client KPIs
- **Retention Rate**: % of clients retained year-over-year
- **Client Lifetime Value**: Average revenue per client
- **Active Project Ratio**: Clients with active projects

---

## 🎯 Best Practices

### Workload Management
1. **Keep utilization between 60-85%** for optimal productivity
2. **Monitor overallocation warnings** weekly
3. **Balance workload** across team members
4. **Plan capacity** before taking new projects

### Time Tracking
1. **Log time daily** for accuracy
2. **Require approvals** for billable hours
3. **Review time entries** weekly
4. **Track non-billable** time for internal analysis

### Project Management
1. **Set realistic budgets** with 10-15% buffer
2. **Assign dedicated project managers**
3. **Monitor health status** regularly
4. **Update actual costs** weekly

### Financial Management
1. **Invoice promptly** after work completion
2. **Follow up** on overdue invoices
3. **Track profit margins** per project type
4. **Maintain 30-day payment terms**

---

## 📝 Next Steps

1. **Create database migrations**:
   ```bash
   php artisan make:migration create_employees_table
   php artisan make:migration create_time_entries_table
   php artisan make:migration create_workload_entries_table
   php artisan make:migration create_projects_table
   php artisan make:migration create_project_assignments_table
   php artisan make:migration create_invoices_table
   php artisan migrate
   ```

2. **Seed sample data**:
   ```bash
   php artisan make:seeder EmployeeSeeder
   php artisan make:seeder ProjectSeeder
   php artisan db:seed
   ```

3. **Test the APIs**:
   ```bash
   # Workload analysis
   curl http://your-app.test/api/v1/workload/analysis?period=current_week \
     -H "Authorization: Bearer TOKEN"

   # Agency dashboard
   curl http://your-app.test/api/v1/agency/dashboard \
     -H "Authorization: Bearer TOKEN"
   ```

---

## 🎉 Summary

**New Models**: 6 (Employee, TimeEntry, WorkloadEntry, Project, ProjectAssignment, Invoice)  
**New Services**: 2 (WorkloadAnalysisService, AgencyManagementService)  
**New Controllers**: 2 (WorkloadController, AgencyController)  
**New API Endpoints**: 2  
**Lines of Code**: ~1,200+

**Capabilities:**
✅ **Employee management** with hierarchies  
✅ **Workload analysis** with overallocation warnings  
✅ **Time tracking** with approval workflow  
✅ **Project management** with team assignments  
✅ **Invoice & billing** automation  
✅ **Agency dashboard** with comprehensive KPIs  
✅ **Capacity planning** and resource optimization  
✅ **Financial tracking** and profitability analysis  

**Your agency is now fully equipped to manage teams, projects, and finances!** 🚀
