<?php

namespace Modules\AgenticAI\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\AgenticAI\Services\Embedding\OpenAIEmbeddingService;

//Sanket v2.0 - replaces keyword-based tool routing with embedding-based semantic intent matching
class SemanticIntentRouter
{
    protected OpenAIEmbeddingService $embeddingService;

    //Sanket v2.0 - category descriptions written as natural language so embedding similarity works well
    protected array $categoryDescriptions = [
        'leave' => 'Apply for leave, check leave balance, approve or reject leave requests, cancel leave, vacation days, sick leave, casual leave, annual leave, time off',
        'recruitment' => 'Hiring process, job openings, candidates, interviews, applications, resumes, offer letters, onboarding new employees, reject candidates',
        'payroll' => 'Salary, pay slips, tax deductions, earnings, wages, salary advances, loans, allowances, increments, payroll processing, salary breakdown',
        'performance' => 'Goals, KPIs, targets, appraisals, performance reviews, ratings, feedback, self-review, manager review, performance cycle',
        'attendance' => 'Check in, check out, attendance history, present, absent, overtime, breaks, site visits, clock in clock out, work hours tracking',
        'training' => 'Training programs, courses, learning, enrollment, quiz, education, skill development, certifications',
        'asset' => 'Laptop, computer, equipment, asset requests, return assets, repair, broken equipment, inventory management',
        'expense' => 'Expense claims, reimbursements, bills, receipts, travel expenses, food allowance, expense reports, approve or reject expenses',
        'task' => 'Tasks, to-do items, assignments, projects, task management, create task, assign task, update task status, due dates',
        'announcement' => 'Company announcements, news, notices, broadcasts, communications, latest updates',
        'shift' => 'Work shifts, schedules, rosters, shift swaps, shift timing, rotation, assign shifts, bulk assign',
        'directory' => 'Employee directory, search employees, contacts, colleagues, profiles, team members, organization structure, reporting hierarchy, update profile',
        'approval' => 'Pending approvals, approve requests, review requests, waiting for approval',
        'document' => 'Company documents, handbooks, forms, files, upload documents, download documents, official documents',
        'policy' => 'Company policies, rules, regulations, guidelines, procedures, remote work policy, dress code, leave policy, HR policies',
        'warning' => 'Warnings, disciplinary actions, violations, infractions, oral warning, termination notice',
        'general_request' => 'General requests, submit requests, request types, admin requests, pending requests',
        'notification' => 'Notifications, alerts, unread messages, mark as read, alert configuration',
        'holiday' => 'Public holidays, days off, calendar, holiday management, upcoming holidays',
        'document_request' => 'Document requests, salary certificate, employment letter, experience letter, visa letter, official certificates',
        'analytics' => 'Analytics, statistics, dashboard, metrics, birthdays, anniversaries, milestones, probation tracking',
        'travel' => 'Air tickets, flights, travel bookings, trip requests, travel approval',
        'apparel' => 'Uniforms, apparel, clothing requests, dress requests',
        'mail' => 'Internal mail, messages, inbox, send mail, compose message, reply to mail',
        'leave_policy' => 'Leave policy configuration, policy templates, accrual rates, leave policy management, carry forward rules',
        'resignation' => 'Resignation, quit, leave company, notice period, last working day, withdraw resignation',
        'probation' => 'Probation period, confirmation, probation status, extend probation, probation completion',
        'promotion' => 'Promotions, career growth, designation change, promotion history',
        'increment' => 'Salary increment, salary increase, raise, salary hike, increment history',
        'organization' => 'Branches, departments, divisions, designations, roles, company structure, organization management, employee transfer',
        'appreciation' => 'Appreciation, recognition, awards, certificates, good work, congratulations',
        'overtime' => 'Overtime, extra hours, OT requests, overtime approval, work extra',
        'reports' => 'Reports, leave report, headcount, employee count, training report, department statistics',
        'biometric' => 'Biometric devices, fingerprint, sync biometric, biometric logs',
    ];

    //Sanket v2.0 - cache for category embeddings (computed once, reused forever)
    protected array $categoryEmbeddings = [];

    public function __construct()
    {
        $this->embeddingService = app(OpenAIEmbeddingService::class);
    }

    //Sanket v2.0 - match user message to top-N categories using cosine similarity of embeddings
    public function classifyIntent(string $message, int $topN = 3): array
    {
        try {
            //Sanket v2.0 - get embedding for user message
            $messageEmbedding = $this->embeddingService->embed($message);

            //Sanket v2.0 - get cached category embeddings
            $categoryEmbeddings = $this->getCategoryEmbeddings();

            //Sanket v2.0 - calculate similarity against each category
            $scores = [];
            foreach ($categoryEmbeddings as $category => $categoryVector) {
                $similarity = $this->cosineSimilarity($messageEmbedding, $categoryVector);
                $scores[$category] = $similarity;
            }

            //Sanket v2.0 - sort by score descending and return top N
            arsort($scores);
            $topCategories = array_slice($scores, 0, $topN, true);

            //Sanket v2.0 - only include categories with similarity > 0.3 (meaningful match)
            $filtered = array_filter($topCategories, fn($score) => $score > 0.3);

            Log::info('SemanticIntentRouter: Intent classified', [
                'message' => substr($message, 0, 80),
                'top_categories' => $filtered,
            ]);

            return array_keys($filtered);
        } catch (\Exception $e) {
            Log::warning('SemanticIntentRouter: Embedding failed, falling back to keywords', [
                'error' => $e->getMessage(),
            ]);
            return []; // Return empty so caller falls back to keyword matching
        }
    }

    //Sanket v2.0 - pre-compute and cache embeddings for all category descriptions
    protected function getCategoryEmbeddings(): array
    {
        if (!empty($this->categoryEmbeddings)) {
            return $this->categoryEmbeddings;
        }

        //Sanket v2.0 - cache embeddings permanently (invalidate only when categories change)
        $cached = Cache::get('ai_category_embeddings');

        if ($cached && is_array($cached) && count($cached) === count($this->categoryDescriptions)) {
            $this->categoryEmbeddings = $cached;
            return $this->categoryEmbeddings;
        }

        //Sanket v2.0 - compute embeddings for each category description
        Log::info('SemanticIntentRouter: Computing category embeddings (first time or cache miss)');

        foreach ($this->categoryDescriptions as $category => $description) {
            $this->categoryEmbeddings[$category] = $this->embeddingService->embed($description);
        }

        //Sanket v2.0 - cache forever (until manually cleared or categories updated)
        Cache::forever('ai_category_embeddings', $this->categoryEmbeddings);

        return $this->categoryEmbeddings;
    }

    protected function cosineSimilarity(array $vecA, array $vecB): float
    {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;
        $count = min(count($vecA), count($vecB));

        for ($i = 0; $i < $count; $i++) {
            $dotProduct += $vecA[$i] * $vecB[$i];
            $normA += $vecA[$i] * $vecA[$i];
            $normB += $vecB[$i] * $vecB[$i];
        }

        if ($normA == 0 || $normB == 0) return 0;
        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
