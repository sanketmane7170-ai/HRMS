<?php

namespace Modules\Recruitment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CandidateScoringRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'application_id' => ['required', 'exists:recruitment_applications,id'],
            'scoring_criteria' => ['required', 'array', 'min:1'],
            'scoring_criteria.*.criterion_id' => ['required', 'string', 'max:100'],
            'scoring_criteria.*.criterion_name' => ['required', 'string', 'max:255'],
            'scoring_criteria.*.score' => ['required', 'integer', 'min:1', 'max:10'],
            'scoring_criteria.*.weight' => ['required', 'numeric', 'min:0.1', 'max:1.0'],
            'scoring_criteria.*.notes' => ['nullable', 'string', 'max:500'],
            'overall_score' => ['required', 'numeric', 'min:1', 'max:100'],
            'scoring_method' => ['required', Rule::in(['weighted_average', 'simple_average', 'custom'])],
            'interviewer_notes' => ['nullable', 'string', 'max:2000'],
            'strengths' => ['nullable', 'array'],
            'strengths.*' => ['string', 'max:255'],
            'weaknesses' => ['nullable', 'array'],  
            'weaknesses.*' => ['string', 'max:255'],
            'recommendation' => ['required', Rule::in(['strongly_recommend', 'recommend', 'neutral', 'not_recommend', 'strongly_not_recommend'])],
            'recommendation_notes' => ['nullable', 'string', 'max:1000'],
            'cultural_fit_score' => ['nullable', 'integer', 'min:1', 'max:10'],
            'technical_skills_score' => ['nullable', 'integer', 'min:1', 'max:10'],
            'communication_score' => ['nullable', 'integer', 'min:1', 'max:10'],
            'leadership_potential_score' => ['nullable', 'integer', 'min:1', 'max:10'],
            'problem_solving_score' => ['nullable', 'integer', 'min:1', 'max:10'],
            'is_final_score' => ['boolean'],
            'next_steps' => ['nullable', 'string', 'max:500'],
            'interview_round' => ['required', 'integer', 'min:1', 'max:10'],
            'interview_type' => ['required', Rule::in(['phone_screening', 'technical', 'behavioral', 'panel', 'final', 'cultural_fit'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'application_id.required' => 'Application selection is required.',
            'application_id.exists' => 'Selected application does not exist.',
            'scoring_criteria.required' => 'At least one scoring criterion is required.',
            'scoring_criteria.min' => 'At least one scoring criterion is required.',
            'scoring_criteria.*.criterion_name.required' => 'Criterion name is required.',
            'scoring_criteria.*.score.required' => 'Score is required for each criterion.',
            'scoring_criteria.*.score.min' => 'Score must be at least 1.',
            'scoring_criteria.*.score.max' => 'Score cannot exceed 10.',
            'scoring_criteria.*.weight.required' => 'Weight is required for each criterion.',
            'scoring_criteria.*.weight.min' => 'Weight must be at least 0.1.',
            'scoring_criteria.*.weight.max' => 'Weight cannot exceed 1.0.',
            'overall_score.required' => 'Overall score is required.',
            'overall_score.min' => 'Overall score must be at least 1.',
            'overall_score.max' => 'Overall score cannot exceed 100.',
            'scoring_method.required' => 'Scoring method is required.',
            'scoring_method.in' => 'Invalid scoring method selected.',
            'recommendation.required' => 'Recommendation is required.',
            'recommendation.in' => 'Invalid recommendation selected.',
            'cultural_fit_score.min' => 'Cultural fit score must be at least 1.',
            'cultural_fit_score.max' => 'Cultural fit score cannot exceed 10.',
            'technical_skills_score.min' => 'Technical skills score must be at least 1.',
            'technical_skills_score.max' => 'Technical skills score cannot exceed 10.',
            'communication_score.min' => 'Communication score must be at least 1.',
            'communication_score.max' => 'Communication score cannot exceed 10.',
            'interview_round.required' => 'Interview round is required.',
            'interview_round.min' => 'Interview round must be at least 1.',
            'interview_round.max' => 'Interview round cannot exceed 10.',
            'interview_type.required' => 'Interview type is required.',
            'interview_type.in' => 'Invalid interview type selected.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Check if user has permission to score candidates
        if ($user->can('score_candidates') || $user->hasRole(['HR Manager', 'Recruiter', 'Admin'])) {
            return true;
        }

        // Allow hiring managers and interviewers for their assigned interviews
        if ($user->hasRole(['Hiring Manager', 'Interviewer'])) {
            $application = \Modules\Recruitment\Entities\Application::find($this->input('application_id'));
            
            if ($application) {
                // Check if it's their department (for hiring managers)
                if ($user->hasRole('Hiring Manager') && $application->job && $application->job->department_id === $user->department_id) {
                    return true;
                }
                
                // Check if they are assigned as interviewer
                $interviews = \Modules\Recruitment\Entities\Interview::where('application_id', $application->id)
                    ->where('interviewer_id', $user->id)
                    ->exists();
                    
                if ($interviews) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize strengths and weaknesses arrays
        if ($this->has('strengths') && is_string($this->strengths)) {
            $this->merge([
                'strengths' => array_filter(array_map('trim', explode(',', $this->strengths)))
            ]);
        }

        if ($this->has('weaknesses') && is_string($this->weaknesses)) {
            $this->merge([
                'weaknesses' => array_filter(array_map('trim', explode(',', $this->weaknesses)))
            ]);
        }

        // Calculate overall score if using weighted average
        if ($this->input('scoring_method') === 'weighted_average' && $this->has('scoring_criteria')) {
            $totalScore = 0;
            $totalWeight = 0;
            
            $scoringCriteria = $this->input('scoring_criteria');
            foreach ($scoringCriteria as $criterion) {
                if (isset($criterion['score'], $criterion['weight'])) {
                    $totalScore += $criterion['score'] * $criterion['weight'];
                    $totalWeight += $criterion['weight'];
                }
            }
            
            if ($totalWeight > 0) {
                $calculatedScore = ($totalScore / $totalWeight) * 10; // Convert to 100 scale
                $this->merge(['calculated_overall_score' => round($calculatedScore, 2)]);
            }
        }
    }

    /**
     * Get validated data with computed metrics.
     */
    public function validatedWithMetrics(): array
    {
        $validated = $this->validated();
        
        // Add scoring metadata
        $validated['scored_by'] = $this->user()->id;
        $validated['scored_at'] = now();
        
        // Calculate composite scores
        $scores = [
            'cultural_fit_score' => $validated['cultural_fit_score'] ?? null,
            'technical_skills_score' => $validated['technical_skills_score'] ?? null,
            'communication_score' => $validated['communication_score'] ?? null,
            'leadership_potential_score' => $validated['leadership_potential_score'] ?? null,
            'problem_solving_score' => $validated['problem_solving_score'] ?? null,
        ];
        
        $validScores = array_filter($scores, fn($score) => $score !== null);
        
        if (!empty($validScores)) {
            $validated['average_component_score'] = round(array_sum($validScores) / count($validScores), 2);
        }
        
        // Add recommendation weight
        $recommendationWeights = [
            'strongly_recommend' => 5,
            'recommend' => 4,
            'neutral' => 3,
            'not_recommend' => 2,
            'strongly_not_recommend' => 1,
        ];
        
        $validated['recommendation_weight'] = $recommendationWeights[$validated['recommendation']] ?? 3;
        
        return $validated;
    }
}