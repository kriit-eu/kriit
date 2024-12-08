Teacher
<div id="app" class="container mt-5">
    <h2>{{ assignment.assignmentName }}</h2>
    <p v-html="renderedInstructions"></p>
    <ul class="list-group">
        <li v-for="(criterion, index) in criteria" :key="criterion.criterionId" class="list-group-item">
            <label class="form-check-label">
                <input
                        type="checkbox"
                        class="form-check-input me-2"
                        v-model="criterion.done"
                        @change="updateCriterion(index)"
                />
                {{ criterion.description }}
            </label>
        </li>
    </ul>

    <!-- Button to trigger modal -->
    <button
            type="button"
            class="btn btn-primary mt-3"
            data-bs-toggle="modal"
            data-bs-target="#solutionModal"
    >
        Submit Solution
    </button>

    <!-- Modal -->
    <div class="modal fade" id="solutionModal" tabindex="-1" aria-labelledby="solutionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="solutionModalLabel">Submit Assignment Solution</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="submitSolution">
                        <div class="mb-3">
                            <label for="solutionUrl" class="form-label">Solution URL</label>
                            <input
                                    type="url"
                                    id="solutionUrl"
                                    class="form-control"
                                    v-model="solutionUrl"
                                    placeholder="Enter solution URL"
                                    required
                            />
                        </div>
                        <p><strong>All criteria must be checked to submit:</strong></p>
                        <ul class="list-group">
                            <li v-for="criterion in criteria" :key="criterion.criterionId" class="list-group-item">
                                <label>
                                    <input
                                            type="checkbox"
                                            class="form-check-input me-2"
                                            v-model="criterion.done"
                                            @change="updateCriterion(criteria.indexOf(criterion))"
                                    />
                                    {{ criterion.description }}
                                </label>
                            </li>
                        </ul>
                        <button
                                type="submit"
                                class="btn btn-success mt-3"
                                :disabled="!canSubmitSolution"
                        >
                            Submit Solution
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const assignment = <?= json_encode($assignment); ?>;

    new Vue({
        el: '#app',
        data: {
            assignment: assignment,
            criteria: [],
            solutionUrl: '',
        },
        created() {
            // Get criteria from assignment
            const assignmentCriteria = this.assignment.criteria; // Object with keys as criterion IDs

            // Convert criteria object to array
            const criteriaArray = Object.values(assignmentCriteria);

            // Get user's done criteria
            const studentData = this.assignment.students[Object.keys(this.assignment.students)[0]];
            const userDoneCriteria = studentData.userDoneCriteria || {};

            // Build criteria array with 'done' status
            this.criteria = criteriaArray.map(criterion => {
                const criterionId = criterion.criterionId;
                const doneCriterion = userDoneCriteria[criterionId.toString()];
                return {
                    criterionId: criterionId,
                    description: criterion.criterionName,
                    done: doneCriterion ? doneCriterion.completed : false
                };
            });

            // Get solution URL
            this.solutionUrl = studentData.solutionUrl || '';
        },
        computed: {
            canSubmitSolution() {
                return (
                    this.criteria.every((criterion) => criterion.done) &&
                    this.solutionUrl.trim() !== ''
                );
            },
            renderedInstructions() {
                // Convert Markdown to HTML
                return marked.parse(this.assignment.assignmentInstructions || '');
            },
        },
        methods: {
            updateCriterion(index) {
                const criterion = this.criteria[index];
                console.log(
                    `Sending update for criterion: "${criterion.description}" with state: ${
                        criterion.done ? 'done' : 'not done'
                    }`
                );
                // Simulate an AJAX request with a delay
                setTimeout(() => {
                    console.log(
                        `Server has updated criterion: "${criterion.description}" to state: ${
                            criterion.done ? 'done' : 'not done'
                        }`
                    );
                }, 500);
            },
            submitSolution() {
                console.log('Submitting solution...');
                console.log('Solution URL:', this.solutionUrl);
                console.log('Criteria:', this.criteria);
                // Simulate an AJAX request with a delay
                setTimeout(() => {
                    alert('Solution submitted successfully!');
                    this.solutionUrl = ''; // Reset solution URL after submission
                }, 500);
            },
        },
    });
</script>
