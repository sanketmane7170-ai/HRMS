<?php

namespace Modules\AgenticAI\Services;

use Illuminate\Support\Facades\Log;
use Modules\AgenticAI\Interfaces\ToolInterface;

class ToolRegistryService
{
    /**
     * Map of keywords to Tool classes.
     * This acts as a semantic router.
     */
    protected array $toolMap = [
        'leave' => [
            'keywords' => ['leave', 'vacation', 'sick', 'holiday', 'off', 'apply', 'balance', 'casual', 'approve leave', 'reject leave', 'update leave', 'cancel leave', 'modify leave'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetLeaveBalanceTool::class,
                \Modules\AgenticAI\Tools\ApplyLeaveTool::class,
                \Modules\AgenticAI\Tools\UpdateLeaveTool::class,
                \Modules\AgenticAI\Tools\CancelLeaveTool::class,
                \Modules\AgenticAI\Tools\ApproveLeaveRequestTool::class,
                \Modules\AgenticAI\Tools\RejectLeaveRequestTool::class,
                \Modules\AgenticAI\Tools\GetSystemTimeTool::class,
            ]
        ],
        'recruitment' => [
            'keywords' => ['hire', 'candidate', 'job', 'opening', 'position', 'recruit', 'interview', 'applicant', 'resume', 'cv', 'feedback', 'score', 'offer', 'offer letter', 'salary', 'create job', 'post job', 'reject candidate', 'onboard'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetJobOpeningsTool::class,
                \Modules\AgenticAI\Tools\GetApplicationsTool::class,
                \Modules\AgenticAI\Tools\ScreenCandidateTool::class,
                \Modules\AgenticAI\Tools\ScheduleInterviewTool::class,
                \Modules\AgenticAI\Tools\ProcessInterviewFeedbackTool::class,
                \Modules\AgenticAI\Tools\IssueOfferLetterTool::class,
                \Modules\AgenticAI\Tools\CreateJobOpeningTool::class,
                \Modules\AgenticAI\Tools\RejectCandidateTool::class,
                \Modules\AgenticAI\Tools\OnboardCandidateTool::class,
            ]
        ],
        'payroll' => [
            'keywords' => ['salary', 'pay', 'slip', 'bonus', 'tax', 'deduction', 'earning', 'wage', 'advance', 'loan', 'breakdown', 'allowance', 'increment', 'raise', 'configure tax', 'tax rule', 'process payroll', 'generate payslips', 'payroll summary', 'approve payroll', 'tax report'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetSalarySlipTool::class,
                \Modules\AgenticAI\Tools\ManageSalaryAdvancesTool::class,
                \Modules\AgenticAI\Tools\GetSalaryBreakdownTool::class,
                \Modules\AgenticAI\Tools\EnrollEmployeeTool::class,
                \Modules\AgenticAI\Tools\ConfigurePayrollTaxTool::class,
                \Modules\AgenticAI\Tools\ProcessPayrollTool::class,
                \Modules\AgenticAI\Tools\GetPayrollSummaryTool::class,
                \Modules\AgenticAI\Tools\ApprovePayrollTool::class,
                \Modules\AgenticAI\Tools\GetTaxReportTool::class,
            ]
        ],
        'performance' => [
            'keywords' => ['goal', 'kpi', 'target', 'appraisal', 'review', 'performance', 'feedback', 'rating', 'create goal', 'set goal', 'new goal', 'add goal', 'make goal', 'update goal', 'delete goal', 'remove goal', 'increase', 'improve', 'achieve'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetMyGoalsTool::class,
                \Modules\AgenticAI\Tools\CreateGoalTool::class,
                \Modules\AgenticAI\Tools\UpdateGoalTool::class,
                \Modules\AgenticAI\Tools\DeleteGoalTool::class,
            ]
        ],
        'attendance' => [
            'keywords' => ['attendance', 'present', 'absent', 'check in', 'check out', 'clock in', 'clock out', 'update attendance', 'history', 'record', 'break', 'visit', 'overtime', 'site visit', 'location', 'holiday', 'new year', 'christmas', 'record overtime'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetAttendanceHistoryTool::class,
                \Modules\AgenticAI\Tools\CheckInTool::class,
                \Modules\AgenticAI\Tools\CheckOutTool::class,
                \Modules\AgenticAI\Tools\UpdateAttendanceTool::class,
                \Modules\AgenticAI\Tools\GetBreaksAndVisitsTool::class,
                \Modules\AgenticAI\Tools\GetSystemTimeTool::class,
                \Modules\AgenticAI\Tools\ManageHolidaysTool::class,
                \Modules\AgenticAI\Tools\ProcessOvertimeTool::class,
            ]
        ],
        'training' => [
            'keywords' => ['train', 'course', 'learn', 'program', 'video', 'education', 'enroll', 'complete training', 'quiz', 'question', 'answer'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetTrainingProgramsTool::class,
                \Modules\AgenticAI\Tools\EnrollInTrainingTool::class,
                \Modules\AgenticAI\Tools\CompleteTrainingTool::class,
                \Modules\AgenticAI\Tools\TrainingQuizTool::class,
            ]
        ],
        'asset' => [
            'keywords' => ['laptop', 'computer', 'mouse', 'keyboard', 'asset', 'equipment', 'repair', 'broken', 'issue', 'fix', 'request asset', 'return asset', 'request laptop', 'need laptop', 'new laptop', 'get laptop', 'request computer', 'need computer', 'request equipment', 'need equipment', 'assign asset', 'inventory'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetMyAssetsTool::class,
                \Modules\AgenticAI\Tools\ReportAssetIssueTool::class,
                \Modules\AgenticAI\Tools\RequestAssetTool::class,
                \Modules\AgenticAI\Tools\ReturnAssetTool::class,
                \Modules\AgenticAI\Tools\AssignAssetTool::class,
                \Modules\AgenticAI\Tools\ManageAssetInventoryTool::class,
            ]
        ],
        'expense' => [
            'keywords' => ['expense', 'claim', 'reimburse', 'bill', 'receipt', 'travel', 'food', 'money', 'refund', 'approve expense', 'reject expense', 'update expense', 'cancel expense', 'expense report'],
            'tools' => [
                \Modules\AgenticAI\Tools\FileExpenseTool::class,
                \Modules\AgenticAI\Tools\GetExpenseStatusTool::class,
                \Modules\AgenticAI\Tools\UpdateExpenseTool::class,
                \Modules\AgenticAI\Tools\CancelExpenseTool::class,
                \Modules\AgenticAI\Tools\ApproveExpenseTool::class,
                \Modules\AgenticAI\Tools\RejectExpenseTool::class,
                \Modules\AgenticAI\Tools\GetAllExpensesTool::class,
                \Modules\AgenticAI\Tools\GetExpenseReportTool::class,
            ]
        ],
        'task' => [
            'keywords' => ['task', 'todo', 'assignment', 'work', 'job', 'create task', 'delete task', 'remove task', 'assign task', 'project', 'assign', 'due', 'status', 'update', 'complete'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetMyTasksTool::class,
                \Modules\AgenticAI\Tools\CreateTaskTool::class,
                \Modules\AgenticAI\Tools\UpdateTaskTool::class,
                \Modules\AgenticAI\Tools\DeleteTaskTool::class,
                \Modules\AgenticAI\Tools\AssignTaskTool::class,
            ]
        ],
        'announcement' => [
            'keywords' => ['announcement', 'news', 'update', 'notice', 'communication', 'message', 'broadcast', 'what\'s new', 'latest', 'make announcement', 'create announcement', 'update announcement', 'delete announcement', 'active announcement', 'upcoming announcement', 'past announcement', 'department announcement'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetAnnouncementsTool::class,
                \Modules\AgenticAI\Tools\GetAnnouncementMetaTool::class,
                \Modules\AgenticAI\Tools\CreateAnnouncementTool::class,
                \Modules\AgenticAI\Tools\UpdateAnnouncementTool::class,
                \Modules\AgenticAI\Tools\DeleteAnnouncementTool::class,
            ]
        ],
        'shift' => [
            'keywords' => ['shift', 'schedule', 'roster', 'rotation', 'timing', 'work hours', 'when do i work', 'my schedule', 'swap shift', 'change shift', 'assign shift', 'shift schedule', 'create shift', 'new shift', 'add shift', 'make shift', 'update shift', 'delete shift', 'bulk assign'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetMyShiftsTool::class,
                \Modules\AgenticAI\Tools\SwapShiftTool::class,
                \Modules\AgenticAI\Tools\RequestShiftChangeTool::class,
                \Modules\AgenticAI\Tools\ManageShiftRosterTool::class,
                \Modules\AgenticAI\Tools\CreateShiftScheduleTool::class,
                \Modules\AgenticAI\Tools\UpdateShiftScheduleTool::class,
                \Modules\AgenticAI\Tools\DeleteShiftScheduleTool::class,
                \Modules\AgenticAI\Tools\BulkAssignShiftTool::class,
            ]
        ],
        'directory' => [
            'keywords' => ['find', 'search', 'employee', 'colleague', 'contact', 'who is', 'directory', 'people', 'staff', 'team member', 'all employees', 'list', 'names', 'update profile', 'update phone', 'change phone', 'update email', 'change email', 'update address', 'change address', 'my phone', 'my email', 'my profile', 'joined', 'joining', 'hired', 'start date', 'tenure', 'manager', 'report to', 'reports to', 'who reports to', 'subordinate', 'heirarchy', 'organization'],
            'tools' => [
                \Modules\AgenticAI\Tools\SearchEmployeesTool::class,
                \Modules\AgenticAI\Tools\UpdateMyProfileTool::class,
                \Modules\AgenticAI\Tools\UpdateHierarchyTool::class,
                \Modules\AgenticAI\Tools\ManageAccessTool::class,
                \Modules\AgenticAI\Tools\ExploreOrganizationTool::class,
                \Modules\AgenticAI\Tools\EnrollEmployeeTool::class,
            ]
        ],
        'approval' => [
            'keywords' => ['approval', 'approve', 'pending', 'request', 'waiting', 'needs attention', 'review'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetPendingApprovalsTool::class,
            ]
        ],
        'document' => [
            'keywords' => ['document', 'handbook', 'form', 'file', 'download', 'official', 'company document', 'upload document', 'my documents', 'approve document'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetCompanyDocumentsTool::class,
                \Modules\AgenticAI\Tools\ApproveDocumentRequestTool::class,
                \Modules\AgenticAI\Tools\UploadEmployeeDocumentTool::class,
                \Modules\AgenticAI\Tools\GetMyDocumentsTool::class,
            ]
        ],
        'policy' => [
            'keywords' => ['policy', 'rule', 'regulation', 'guideline', 'procedure', 'remote work', 'dress code', 'how to', 'can i', 'allowed to', 'limit', 'rules', 'process', 'create policy', 'update policy'],
            'tools' => [
                \Modules\AgenticAI\Tools\SearchCompanyPoliciesTool::class,
                \Modules\AgenticAI\Tools\KnowledgeRetrieverTool::class,
                \Modules\AgenticAI\Tools\ExportDocumentTool::class,
                \Modules\AgenticAI\Tools\CreatePolicyTool::class,
                \Modules\AgenticAI\Tools\UpdatePolicyTool::class,
            ]
        ],
        'warning' => [
            'keywords' => ['warning', 'disciplinary', 'notice', 'violation', 'infraction', 'acknowledge warning', 'issue warning', 'oral warning', 'termination'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetMyWarningsTool::class,
                \Modules\AgenticAI\Tools\AcknowledgeWarningTool::class,
                \Modules\AgenticAI\Tools\IssueWarningTool::class,
            ]
        ],
        'general_request' => [
            'keywords' => ['request', 'need', 'want', 'submit', 'ask for', 'general', 'my requests', 'delete request', 'cancel request', 'how many requests', 'update request', 'pending requests', 'approve request', 'reject request', 'admin request', 'request type', 'manage requests'],
            'tools' => [
                \Modules\AgenticAI\Tools\CreateGeneralRequestTool::class,
                \Modules\AgenticAI\Tools\GetMyGeneralRequestsTool::class,
                \Modules\AgenticAI\Tools\UpdateGeneralRequestTool::class,
                \Modules\AgenticAI\Tools\DeleteGeneralRequestTool::class,
                \Modules\AgenticAI\Tools\ProcessAdminRequestTool::class,
                \Modules\AgenticAI\Tools\ManageRequestTypesTool::class,
            ]
        ],
        'notification' => [
            'keywords' => ['notification', 'alert', 'message', 'unread', 'notify', 'mark read', 'delete notification', 'who gets alert', 'alert configuration', 'notify manager', 'notify admin'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetMyNotificationsTool::class,
                \Modules\AgenticAI\Tools\MarkNotificationReadTool::class,
                \Modules\AgenticAI\Tools\DeleteNotificationTool::class,
                \Modules\AgenticAI\Tools\ConfigureAlertRecipientsTool::class,
            ]
        ],
        'holiday' => [
            'keywords' => ['holiday', 'public holiday', 'days off', 'calendar', 'when is', 'manage holiday', 'create holiday', 'add holiday', 'remove holiday'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetHolidaysTool::class,
                \Modules\AgenticAI\Tools\ManageHolidaysTool::class,
            ]
        ],
        'performance_review' => [
            'keywords' => ['performance review', 'review', 'rating', 'evaluation', 'appraisal', 'feedback', 'self review', 'acknowledge review', 'submit rating', 'give feedback', 'kpi', 'goal', 'assign kpi', 'set goal', 'review cycle', 'create review', 'manager review'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetMyPerformanceReviewsTool::class,
                \Modules\AgenticAI\Tools\SubmitSelfReviewTool::class,
                \Modules\AgenticAI\Tools\AcknowledgeReviewTool::class,
                \Modules\AgenticAI\Tools\SubmitPerformanceRatingTool::class,
                \Modules\AgenticAI\Tools\ManageKPIsTool::class,
                \Modules\AgenticAI\Tools\CreateReviewCycleTool::class,
                \Modules\AgenticAI\Tools\GetReviewCycleStatusTool::class,
                \Modules\AgenticAI\Tools\SubmitManagerReviewTool::class,
            ]
        ],
        'document_request' => [
            'keywords' => ['document', 'certificate', 'letter', 'employment letter', 'salary certificate', 'experience letter', 'official document', 'update document request', 'cancel document request', 'request letter', 'need letter', 'visa letter', 'visa', 'employment', 'salary cert'],
            'tools' => [
                \Modules\AgenticAI\Tools\CreateDocumentRequestTool::class,
                \Modules\AgenticAI\Tools\UpdateDocumentRequestTool::class,
                \Modules\AgenticAI\Tools\CancelDocumentRequestTool::class,
            ]
        ],
        'analytics' => [
            'keywords' => ['analytics', 'stats', 'statistics', 'summary', 'dashboard', 'metrics', 'my stats', 'birthday', 'anniversary', 'probation', 'milestone', 'celebration', 'expired', 'expiring', 'expiry'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetAnalyticsSummaryTool::class,
                \Modules\AgenticAI\Tools\GetMilestonesTool::class,
                \Modules\AgenticAI\Tools\CheckExpiredItemsTool::class,
            ]
        ],
        'files' => [
            'keywords' => ['files', 'uploads', 'my files', 'documents', 'uploaded', 'upload file', 'delete file'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetMyFilesTool::class,
                \Modules\AgenticAI\Tools\UploadFileTool::class,
                \Modules\AgenticAI\Tools\DeleteFileTool::class,
            ]
        ],
        'travel' => [
            'keywords' => ['air ticket', 'flight', 'travel', 'booking', 'trip', 'request ticket', 'cancel ticket', 'pending ticket', 'approve ticket'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetMyAirTicketsTool::class,
                \Modules\AgenticAI\Tools\RequestAirTicketTool::class,
                \Modules\AgenticAI\Tools\CancelAirTicketTool::class,
                \Modules\AgenticAI\Tools\ProcessAdminRequestTool::class,
            ]
        ],
        'apparel' => [
            'keywords' => ['uniform', 'apparel', 'clothing', 'dress', 'request uniform', 'update apparel', 'pending apparel', 'approve apparel'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetMyApparelTool::class,
                \Modules\AgenticAI\Tools\RequestApparelTool::class,
                \Modules\AgenticAI\Tools\UpdateApparelRequestTool::class,
                \Modules\AgenticAI\Tools\ProcessAdminRequestTool::class,
            ]
        ],
        'mail' => [
            'keywords' => ['mail', 'internal mail', 'message', 'inbox', 'sent', 'send mail', 'reply', 'delete mail', 'send message', 'message to', 'email to', 'mail to', 'send to', 'write to', 'compose'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetMyMailsTool::class,
                \Modules\AgenticAI\Tools\SendMailTool::class,
                \Modules\AgenticAI\Tools\ReplyToMailTool::class,
                \Modules\AgenticAI\Tools\DeleteMailTool::class,
            ]
        ],
        'leave_policy' => [
            'keywords' => ['leave policy', 'policy template', 'configure policy', 'create policy', 'policy configuration', 'accrual', 'leave template', 'policy management'],
            'tools' => [
                \Modules\AgenticAI\Tools\ConfigureLeavePolicyTool::class,
            ]
        ],
        'resignation' => [
            'keywords' => ['resign', 'resignation', 'quit', 'leave company', 'notice period', 'last working day', 'submit resignation', 'check resignation', 'resignation status', 'approve resignation', 'reject resignation', 'withdraw resignation', 'cancel resignation'],
            'tools' => [
                \Modules\AgenticAI\Tools\SubmitResignationTool::class,
                \Modules\AgenticAI\Tools\GetMyResignationStatusTool::class,
                \Modules\AgenticAI\Tools\ApproveResignationTool::class,
                \Modules\AgenticAI\Tools\WithdrawResignationTool::class,
            ]
        ],
        'probation' => [
            'keywords' => ['probation', 'probation period', 'confirmation', 'probation status', 'probation end', 'probation completion', 'confirm probation', 'extend probation'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetProbationStatusTool::class,
                \Modules\AgenticAI\Tools\ConfirmProbationTool::class,
                \Modules\AgenticAI\Tools\ExtendProbationTool::class,
            ]
        ],
        'promotion' => [
            'keywords' => ['promotion', 'promote', 'promoted', 'career growth', 'designation change', 'promotion history', 'issue promotion'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetMyPromotionHistoryTool::class,
                \Modules\AgenticAI\Tools\IssuePromotionLetterTool::class,
            ]
        ],
        'increment' => [
            'keywords' => ['increment', 'salary increase', 'raise', 'salary hike', 'increment history', 'issue increment'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetMyIncrementHistoryTool::class,
                \Modules\AgenticAI\Tools\IssueIncrementLetterTool::class,
            ]
        ],
        'organization' => [
            'keywords' => ['branch', 'department', 'division', 'designation', 'role', 'company structure', 'create branch', 'new branch', 'add branch', 'manage branch', 'create division', 'new division', 'add division', 'create designation', 'new designation', 'add designation', 'enroll employee', 'new employee', 'hire employee', 'create employee', 'add employee', 'transfer employee', 'move employee'],
            'tools' => [
                \Modules\AgenticAI\Tools\ManageOrganizationTool::class,
                \Modules\AgenticAI\Tools\EnrollEmployeeTool::class,
                \Modules\AgenticAI\Tools\ExploreOrganizationTool::class,
                \Modules\AgenticAI\Tools\UpdateHierarchyTool::class,
                \Modules\AgenticAI\Tools\ManageAccessTool::class,
                \Modules\AgenticAI\Tools\TransferEmployeeTool::class,
            ],
        ],
        'appreciation' => [
            'keywords' => ['appreciation', 'recognize', 'award', 'certificate', 'thank you', 'good work', 'well done', 'congratulations', 'my appreciations', 'issue appreciation'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetMyAppreciationsTool::class,
                \Modules\AgenticAI\Tools\IssueAppreciationTool::class,
            ]
        ],
        'overtime' => [
            'keywords' => ['overtime', 'extra hours', 'ot', 'work extra', 'request overtime', 'my overtime', 'approve overtime'],
            'tools' => [
                \Modules\AgenticAI\Tools\RequestOvertimeTool::class,
                \Modules\AgenticAI\Tools\GetMyOvertimeTool::class,
                \Modules\AgenticAI\Tools\ApproveOvertimeTool::class,
            ]
        ],
        'reports' => [
            'keywords' => ['report', 'analytics', 'statistics', 'summary', 'leave report', 'headcount', 'employee count', 'department count', 'training report'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetTrainingReportTool::class,
                \Modules\AgenticAI\Tools\GetLeaveReportTool::class,
                \Modules\AgenticAI\Tools\GetHeadcountReportTool::class,
            ]
        ],
        'biometric' => [
            'keywords' => ['biometric', 'fingerprint', 'device', 'sync', 'biometric logs', 'sync biometric'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetBiometricLogsTool::class,
                \Modules\AgenticAI\Tools\SyncBiometricDataTool::class,
            ]
        ],
        'organization_chart' => [
            'keywords' => ['org chart', 'organization chart', 'hierarchy', 'reporting structure', 'who reports to'],
            'tools' => [
                \Modules\AgenticAI\Tools\GetOrgChartTool::class,
            ]
        ],
        'notifications' => [
            'keywords' => ['notification', 'notify', 'alert', 'broadcast', 'send notification', 'bulk notification'],
            'tools' => [
                \Modules\AgenticAI\Tools\SendBulkNotificationTool::class,
            ]
        ],
    ];

    //Sanket v2.0 - added DatabaseExplorerTool as global so AI can query any data even when no specific tool exists
    protected array $globalTools = [
        \Modules\AgenticAI\Tools\GetSystemTimeTool::class,
        \Modules\AgenticAI\Tools\ExportDocumentTool::class, // Always available for exports/drafts
        \Modules\AgenticAI\Tools\DatabaseExplorerTool::class, // Universal read-only DB access
        \Modules\AgenticAI\Tools\KnowledgeRetrieverTool::class, // Always available for policy/knowledge queries
    ];

    /**
     * Get relevant tools based on user message.
     * Sanket v2.0 - Hybrid routing: Semantic (embeddings) first, keyword fallback second.
     */
    public function getToolsForIntent(string $message): array
    {
        $selectedToolClasses = $this->globalTools;

        //Sanket v2.0 - try semantic routing first (embedding-based intent classification)
        $semanticCategories = $this->getSemanticCategories($message);

        if (!empty($semanticCategories)) {
            //Sanket v2.0 - semantic router found matching categories, load their tools
            foreach ($semanticCategories as $category) {
                if (isset($this->toolMap[$category])) {
                    $selectedToolClasses = array_merge($selectedToolClasses, $this->toolMap[$category]['tools']);
                }
            }
            Log::info('ToolRegistry: Semantic routing matched', ['categories' => $semanticCategories]);
        }

        //Sanket v2.0 - also run keyword matching to catch anything semantic missed (hybrid approach)
        $message = strtolower($message);
        foreach ($this->toolMap as $category => $data) {
            foreach ($data['keywords'] as $keyword) {
                if (str_contains($message, $keyword)) {
                    $selectedToolClasses = array_merge($selectedToolClasses, $data['tools']);
                    break;
                }
            }
        }

        // Deduplicate
        $selectedToolClasses = array_unique($selectedToolClasses);

        // Instantiate
        $toolInstances = [];
        foreach ($selectedToolClasses as $class) {
            try {
                if (class_exists($class)) {
                    $toolInstances[] = new $class();
                }
            } catch (\Throwable $e) {
                Log::warning("AI Tool instantiation failed for {$class}: " . $e->getMessage());
            }
        }

        return $toolInstances;
    }

    //Sanket v2.0 - use SemanticIntentRouter for embedding-based intent classification with graceful fallback
    protected function getSemanticCategories(string $message): array
    {
        //Sanket v2.0 - skip the embeddings API call for simple greetings and trivial phrases
        //These messages have no HR intent so semantic routing would return nothing useful anyway
        if ($this->isSimpleGreeting($message)) {
            return [];
        }

        try {
            $router = new SemanticIntentRouter();
            return $router->classifyIntent($message, 3);
        } catch (\Exception $e) {
            Log::warning('ToolRegistry: Semantic routing failed, using keywords only', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    //Sanket v2.0 - detect greetings and trivial phrases that have no HR tool intent
    protected function isSimpleGreeting(string $message): bool
    {
        $patterns = [
            '/^(hi|hello|hey|hiya|howdy)\b/i',
            '/^good\s*(morning|afternoon|evening|night|day)\b/i',
            '/^(how are you|how do you do|what\'s up|sup|yo)\b/i',
            '/^(thanks|thank you|thx|ty|cheers|ok|okay|got it|sure|great|noted|understood|alright|fine|no problem)\b/i',
            '/^(bye|goodbye|see you|cya|take care|ttyl|later)\b/i',
            '/^(who are you|what are you|what can you do|introduce yourself)\b/i',
        ];
        $msg = trim($message);
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $msg)) {
                return true;
            }
        }
        return false;
    }
}
