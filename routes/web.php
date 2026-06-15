<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WhiteboardController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SalaryHistoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\RecurringExpenseController;
use App\Http\Controllers\ProjectBudgetController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveBalanceController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\EmployeeBreakController;
use App\Http\Controllers\WorkScheduleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AttendanceController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.change.post');
});

Route::middleware(['auth', 'password.change'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('index');
        Route::post('/', [DepartmentController::class, 'store'])->name('store');
        Route::put('/{department}', [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::post('/{employee}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
        Route::post('/{employee}/attachments', [EmployeeController::class, 'storeAttachment'])->name('attachments.store');
        Route::delete('/{employee}/attachments/{userAttachment}', [EmployeeController::class, 'destroyAttachment'])->name('attachments.destroy');
        Route::post('/{employee}/send-job-offer', [EmployeeController::class, 'sendJobOffer'])->name('send-job-offer');
        Route::post('/{employee}/send-contract', [EmployeeController::class, 'sendContract'])->name('send-contract');
        Route::post('/{employee}/regenerate-documents', [EmployeeController::class, 'regenerateDocuments'])->name('regenerate-documents');
    });

    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
        Route::post('/{project}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');
        Route::post('/{project}/members', [ProjectController::class, 'storeMember'])->name('members.store');
        Route::delete('/{project}/members/{member}', [ProjectController::class, 'destroyMember'])->name('members.destroy');
        Route::post('/{project}/attachments', [ProjectController::class, 'storeAttachment'])->name('attachments.store');
        Route::delete('/{project}/attachments/{attachment}', [ProjectController::class, 'destroyAttachment'])->name('attachments.destroy');
    });

    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::post('/', [ClientController::class, 'store'])->name('store');
        Route::get('/{client}', [ClientController::class, 'show'])->name('show');
        Route::post('/{client}', [ClientController::class, 'update'])->name('update');
        Route::delete('/{client}', [ClientController::class, 'destroy'])->name('destroy');
        Route::post('/{client}/attachments', [ClientController::class, 'storeAttachment'])->name('attachments.store');
        Route::delete('/{client}/attachments/{userAttachment}', [ClientController::class, 'destroyAttachment'])->name('attachments.destroy');
    });

    Route::prefix('expense-categories')->name('expense-categories.')->group(function () {
        Route::get('/', [ExpenseCategoryController::class, 'index'])->name('index');
        Route::post('/', [ExpenseCategoryController::class, 'store'])->name('store');
        Route::post('/{category}', [ExpenseCategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [ExpenseCategoryController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::post('/', [ExpenseController::class, 'store'])->name('store');
        Route::post('/{expense}', [ExpenseController::class, 'update'])->name('update');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('conversations')->name('conversations.')->group(function () {
        Route::get('/', [ConversationController::class, 'index'])->name('index');
        Route::post('/', [ConversationController::class, 'store'])->name('store');
        Route::get('/{conversation}', [ConversationController::class, 'show'])->name('show');
        Route::post('/{conversation}/messages', [ConversationController::class, 'sendMessage'])->name('messages.store');
        Route::patch('/{conversation}/messages/{message}/react', [ConversationController::class, 'react'])->name('messages.react');
        Route::delete('/{conversation}', [ConversationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('calls')->name('calls.')->group(function () {
        Route::get('/', [CallController::class, 'index'])->name('index');
        Route::post('/', [CallController::class, 'store'])->name('store');
        Route::get('/{call}', [CallController::class, 'show'])->name('show');
        Route::get('/{call}/token', [CallController::class, 'token'])->name('token');
        Route::post('/{call}/join', [CallController::class, 'join'])->name('join');
        Route::post('/{call}/reject', [CallController::class, 'reject'])->name('reject');
        Route::post('/{call}/leave', [CallController::class, 'leave'])->name('leave');
        Route::post('/{call}/end', [CallController::class, 'end'])->name('end');
        Route::post('/{call}/participant', [CallController::class, 'updateParticipant'])->name('participant.update');
        Route::post('/{call}/events', [CallController::class, 'logEvent'])->name('events.store');
    });

    Route::prefix('whiteboards')->name('whiteboards.')->group(function () {
        Route::get('/', [WhiteboardController::class, 'index'])->name('index');
        Route::post('/', [WhiteboardController::class, 'store'])->name('store');
        Route::get('/{whiteboard}', [WhiteboardController::class, 'show'])->name('show');
        Route::post('/{whiteboard}/save', [WhiteboardController::class, 'save'])->name('save');
        Route::patch('/{whiteboard}/rename', [WhiteboardController::class, 'rename'])->name('rename');
        Route::delete('/{whiteboard}', [WhiteboardController::class, 'destroy'])->name('destroy');
        Route::post('/{whiteboard}/share', [WhiteboardController::class, 'share'])->name('share');
        Route::delete('/{whiteboard}/shares/{share}', [WhiteboardController::class, 'unshare'])->name('unshare');
        Route::post('/{whiteboard}/attach', [WhiteboardController::class, 'attach'])->name('attach');
    });

    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::post('/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
    });

    // ── Finance ──────────────────────────────────────────────────────────────

    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::post('/', [PayrollController::class, 'store'])->name('store');
        Route::post('/{payroll}/approve', [PayrollController::class, 'approve'])->name('approve');
        Route::post('/{payroll}/mark-paid', [PayrollController::class, 'markPaid'])->name('mark-paid');
        Route::delete('/{payroll}', [PayrollController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('salary-histories')->name('salary-histories.')->group(function () {
        Route::get('/', [SalaryHistoryController::class, 'index'])->name('index');
        Route::post('/', [SalaryHistoryController::class, 'store'])->name('store');
        Route::post('/{salaryHistory}', [SalaryHistoryController::class, 'update'])->name('update');
        Route::delete('/{salaryHistory}', [SalaryHistoryController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::post('/{invoice}', [InvoiceController::class, 'update'])->name('update');
        Route::post('/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('payment');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('recurring-expenses')->name('recurring-expenses.')->group(function () {
        Route::get('/', [RecurringExpenseController::class, 'index'])->name('index');
        Route::post('/', [RecurringExpenseController::class, 'store'])->name('store');
        Route::post('/{recurringExpense}', [RecurringExpenseController::class, 'update'])->name('update');
        Route::delete('/{recurringExpense}', [RecurringExpenseController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('project-budgets')->name('project-budgets.')->group(function () {
        Route::get('/', [ProjectBudgetController::class, 'index'])->name('index');
        Route::post('/', [ProjectBudgetController::class, 'store'])->name('store');
        Route::post('/{projectBudget}', [ProjectBudgetController::class, 'update'])->name('update');
        Route::delete('/{projectBudget}', [ProjectBudgetController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('email-templates')->name('email-templates.')->group(function () {
        Route::get('/', [EmailTemplateController::class, 'index'])->name('index');
        Route::put('/{type}', [EmailTemplateController::class, 'update'])->name('update');
    });

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
        Route::post('/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('update-permissions');
    });

    // ── HR ───────────────────────────────────────────────────────────────────

    Route::prefix('leave-types')->name('leave-types.')->group(function () {
        Route::get('/', [LeaveTypeController::class, 'index'])->name('index');
        Route::post('/', [LeaveTypeController::class, 'store'])->name('store');
        Route::post('/{leaveType}', [LeaveTypeController::class, 'update'])->name('update');
        Route::delete('/{leaveType}', [LeaveTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
        Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
        Route::post('/{leaveRequest}', [LeaveRequestController::class, 'update'])->name('update');
        Route::post('/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('approve');
        Route::post('/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('reject');
        Route::delete('/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('leave-balances')->name('leave-balances.')->group(function () {
        Route::get('/', [LeaveBalanceController::class, 'index'])->name('index');
        Route::post('/', [LeaveBalanceController::class, 'store'])->name('store');
        Route::post('/{leaveBalance}', [LeaveBalanceController::class, 'update'])->name('update');
        Route::delete('/{leaveBalance}', [LeaveBalanceController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('overtime-requests')->name('overtime-requests.')->group(function () {
        Route::get('/', [OvertimeRequestController::class, 'index'])->name('index');
        Route::post('/', [OvertimeRequestController::class, 'store'])->name('store');
        Route::post('/{overtimeRequest}', [OvertimeRequestController::class, 'update'])->name('update');
        Route::post('/{overtimeRequest}/approve', [OvertimeRequestController::class, 'approve'])->name('approve');
        Route::post('/{overtimeRequest}/reject', [OvertimeRequestController::class, 'reject'])->name('reject');
        Route::delete('/{overtimeRequest}', [OvertimeRequestController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('employee-breaks')->name('employee-breaks.')->group(function () {
        Route::get('/', [EmployeeBreakController::class, 'index'])->name('index');
        Route::post('/', [EmployeeBreakController::class, 'store'])->name('store');
        Route::post('/{employeeBreak}', [EmployeeBreakController::class, 'update'])->name('update');
        Route::delete('/{employeeBreak}', [EmployeeBreakController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('work-schedules')->name('work-schedules.')->group(function () {
        Route::get('/', [WorkScheduleController::class, 'index'])->name('index');
        Route::post('/', [WorkScheduleController::class, 'store'])->name('store');
        Route::post('/{workSchedule}', [WorkScheduleController::class, 'update'])->name('update');
        Route::delete('/{workSchedule}', [WorkScheduleController::class, 'destroy'])->name('destroy');
    });

    // ── Notifications ─────────────────────────────────────────────────────────

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    });

    // ── Activity Logs ─────────────────────────────────────────────────────────

    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
    });

    // ── Attendance ────────────────────────────────────────────────────────────

    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::post('/clock-in', [AttendanceController::class, 'clockIn'])->name('clock-in');
        Route::post('/clock-out', [AttendanceController::class, 'clockOut'])->name('clock-out');
        Route::post('/break-start', [AttendanceController::class, 'breakStart'])->name('break-start');
        Route::post('/break-end', [AttendanceController::class, 'breakEnd'])->name('break-end');
        Route::post('/', [AttendanceController::class, 'store'])->name('store');
        Route::post('/{attendance}', [AttendanceController::class, 'update'])->name('update');
        Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
    });

    // ── Reports ───────────────────────────────────────────────────────────────

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/payroll/{payrollRun}', [ReportController::class, 'payrollPdf'])->name('payroll');
        Route::get('/expenses/excel', [ReportController::class, 'expensesExcel'])->name('expenses-excel');
        Route::get('/expenses', [ReportController::class, 'expensesPdf'])->name('expenses');
        Route::get('/attendance/{employee}', [ReportController::class, 'attendancePdf'])->name('attendance');
        Route::get('/project/{project}', [ReportController::class, 'projectPdf'])->name('project');
    });
});

Route::redirect('/', '/whiteboards');
