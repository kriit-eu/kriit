<style>
    h2 {
        margin-top: 20px;
    }
</style>

Student

<div id="app" class="container mt-5">

    <h2>{{ assignment.assignmentName }}</h2>
    <p v-html="renderedInstructions"></p>

    <!-- Comments -->
    <div v-if="comments.length > 0" style="max-height: 500px; overflow-y: auto;">
        <div v-for="comment in comments" class="card mb-3">
            <div class="card-body">
                <p>
                    {{ comment.createdAt }} <strong>{{ comment.name || 'Tundmatu' }}</strong><br>
                    <em>{{ comment.comment }}</em>
                </p>
            </div>
        </div>
    </div>
    <div v-else>
        <p>Kommentaare pole.</p>
    </div>
    <div id="commentSection" class="mb-3">
        <div class="input-group my-3">
            <textarea
                id="studentComment"
                class="form-control"
                rows="3"
                placeholder="Lisa kommentaar siia..."
                v-model="studentComment"
                required>
            </textarea>
            <button
                type="button"
                class="btn btn-success"
                :disabled="!studentComment.trim()"
                @click="submitComment">
                Esita
            </button>
        </div>
    </div>

    <!-- Solution Submission Form -->
    <form @submit.prevent="submitSolution">
        <h2><strong>Lahendamine</strong></h2>
        <p>Loe järgmist loetelu ja märgi iga kriteeriumi juurde linnuke kohe, kui oled selle täitnud, enne järgmise
            kriteeriumi kallale asumist.</p>
        <ul class="list-group">
            <li
                v-for="(criterion, index) in criteria"
                :key="criterion.criterionId"
                class="list-group-item">
                <label>
                    <input
                        type="checkbox"
                        class="form-check-input me-2"
                        v-model="criterion.done"
                        @change="updateCriterion(index)"/>
                    {{ criterion.description }}
                </label>
            </li>
        </ul>

        <h2><strong>Esitamine</strong></h2>
        <p>Olles kõik kriteeriumid ära täitnud, sisesta siia link, kust saab sinu tööd näha ja vajuta "Esita" nuppu.</p>
        <div class="input-group my-3">
            <input
                type="url"
                id="solutionUrl"
                class="form-control"
                v-model="solutionUrl"
                placeholder="Enter solution URL"
                required/>
            <button
                type="submit"
                class="btn btn-success"
                :disabled="!canSubmitSolution">
                Esita
            </button>
        </div>
    </form>

</div>

<!-- Include required dependencies via CDN -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    const assignment = <?= json_encode($assignment); ?>;
    const userId = <?= json_encode($this->auth->userId); ?>;

    new Vue({
        el: '#app',
        data: {
            assignment: assignment,
            criteria: [],
            solutionUrl: '',
            studentComment: '',
            userId: userId
        },
        created() {
            // Extract criteria from the assignment object
            const assignmentCriteria = this.assignment.criteria;
            const criteriaArray = Object.values(assignmentCriteria);

            // Get student's data
            const studentData = this.assignment.students[Object.keys(this.assignment.students)[0]];
            const userDoneCriteria = studentData.userDoneCriteria || {};

            // Build criteria array with 'done' status
            this.criteria = criteriaArray.map((criterion) => {
                const criterionId = criterion.criterionId;
                const doneCriterion = userDoneCriteria[criterionId.toString()];
                return {
                    criterionId: criterionId,
                    description: criterion.criterionName,
                    done: doneCriterion ? doneCriterion.completed : false,
                };
            });

            // Set the solution URL if it exists
            this.solutionUrl = studentData.solutionUrl || '';

            // Initialize comments
            this.comments = studentData.comments || [];
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
            submitComment() {
                axios.post('/assignments/saveStudentComment', {
                    studentId: this.userId,
                    studentName: this.assignment.students[this.userId].studentName,
                    assignmentId: this.assignment.assignmentId,
                    comment: this.studentComment,
                    teacherName: this.assignment.teacherName,
                    teacherId: this.assignment.teacherId,
                })
                    .then(response => {
                        // Re-initialize comments and empty textarea after successful submission
                        this.comments.push({
                            createdAt: new Date().toLocaleString(),
                            name: this.assignment.students[this.userId].studentName,
                            comment: this.studentComment,
                        });
                        this.studentComment = '';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            },
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
