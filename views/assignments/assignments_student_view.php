<style>
    h2 {
        margin-top: 20px;
    }

    .comments-section {
        max-height: 500px;
        overflow-y: auto;
    }

    .criterion-done {
        background-color: #dff0d8;
        color: #3c763d;
        text-decoration: line-through;
    }

    #commentText.form-control:focus {
        border-color: #80bdff;
        /* inverse shadow */
        box-shadow: inset 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }

    .criterion-unsaved {
        background-color: #f2dede;
        color: #a94442;
        text-decoration: line-through;
    }
</style>

Student

<div id="app" class="container mt-5">
    <div class="row">
        <!-- Lahenduse pealkiri ja juhised -->
        <div class="col-lg-8 col-md-7 col-sm-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title">{{ assignment.assignmentName }}</h2>
                </div>
                <div class="card-body">
                    <p v-html="renderedInstructions"></p>
                </div>
            </div>

            <!-- Lahendamise sektsioon -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title"><strong>Lahendamine</strong></h2>
                </div>
                <div class="card-body">
                    <p>
                        Loe järgmist loetelu ja märgi iga kriteeriumi juurde linnuke kohe, kui oled selle täitnud, enne
                        järgmise kriteeriumi kallale asumist.
                    </p>
                    <ul class="list-group">
                        <li
                                v-for="(criterion, index) in criteria"
                                :key="criterion.criterionId"
                                class="list-group-item"
                                :class="criterion.done ? 'criterion-done' : ''"
                                data-bs-toggle="tooltip">
                            <label>
                                <input
                                        type="checkbox"
                                        class="form-check-input me-2"
                                        v-model="criterion.done"
                                        @change="saveUserDoneCriteria(criterion)"/>
                                <span
                                        v-if="criterion.unsaved"
                                        class="text-warning ms-2"
                                        data-bs-toggle="tooltip"
                                        v-tooltip="criterion.tooltipText"
                                >&#9888;</span>
                                {{ criterion.description }}
                            </label>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Esitamise sektsioon -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title"><strong>Esitamine</strong></h2>
                </div>
                <div class="card-body">
                    <p>Olles kõik kriteeriumid ära täitnud, sisesta siia link, kust saab sinu tööd näha ja vajuta
                        "Esita"
                        nuppu.</p>
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
                </div>
            </div>
        </div>

        <!-- Kommentaarium -->
        <div class="col-lg-4 col-md-5 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Kommentaarid</h3>
                </div>
                <div class="card-body">
                    <div v-if="comments.length > 0">
                        <div v-for="comment in comments" class="mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <p>
                                        {{ comment.createdAt }} <strong>{{ comment.name || 'Tundmatu' }}</strong><br>
                                        <em>{{ comment.comment }}</em>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <p>Kommentaare pole.</p>
                    </div>
                    <div id="commentSection" class="mt-3">
                        <div class="input-group">
                            <textarea
                                    id="commentText"
                                    class="form-control"
                                    :class="commentUnsaved ? 'is-invalid' : ''"
                                    rows="3"
                                    placeholder="Lisa kommentaar siia..."
                                    v-model="commentText"
                                    required>
                            </textarea>
                            <button
                                    type="button"
                                    class="btn btn-success"
                                    :disabled="!commentText.trim()"
                                    @click="submitComment">
                                Esita
                            </button>
                        </div>
                        <span v-if="commentUnsaved" class="text-danger">{{commentUnsavedReason}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Include required dependencies via CDN -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    const assignment = <?= json_encode($assignment); ?>;
    const userId = <?= json_encode($this->auth->userId); ?>;

    Vue.directive('tooltip', {
        inserted(el, binding) {
            new bootstrap.Tooltip(el, {
                title: binding.value,
                placement: 'top', // You can customize the placement
            });
        },
        update(el, binding) {
            el.setAttribute('data-bs-original-title', binding.value);
        }
    });

    const vue = new Vue({
            el: '#app',
            data: {
                assignment: assignment,
                criteria: [],
                solutionUrl: '',
                userId: userId,
                commentText: '',
                commentUnsaved: false,
                commentUnsavedReason: '',
            },
            created() {
                // Extract criteria from the assignment object
                const assignmentCriteria = this.assignment.criteria;
                const criteriaArray = Object.values(assignmentCriteria);

                // Get student's data
                const studentData = this.assignment.students[Object.keys(this.assignment.students)[0]];
                const userDoneCriteria = studentData.userDoneCriteria || {};

                // Build criteria array with 'done' status and set the appropriate class
                this.criteria = criteriaArray.map((criterion) => {
                    const criterionId = criterion.criterionId;
                    const doneCriterion = userDoneCriteria[criterionId.toString()];
                    const done = doneCriterion ? doneCriterion.completed : false;
                    return {
                        criterionId: criterionId,
                        description: criterion.criterionName,
                        done: done,
                        class: done ? 'criterion-done' : '',
                        tooltipText: done ? 'Tingimus on salvestatud' : ''
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
                async submitComment() {
                    try {
                        ajax('api/assignments/addComment', {
                            assignmentId: this.assignment.assignmentId,
                            comment: this.commentText,
                        }, (res) => {
                            this.commentUnsaved = false;
                            this.comments.push({
                                createdAt: new Date().toLocaleString('et-EE', {
                                    timeZone: 'Europe/Tallinn',
                                    year: 'numeric',
                                    month: '2-digit',
                                    day: '2-digit',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }).replace(',', ''),
                                name: this.assignment.students[this.userId].studentName,
                                comment: this.commentText,
                            });
                            this.commentText = '';
                        }, (res) => {
                            this.commentUnsaved = true;
                            this.commentUnsavedReason = '⚠️ Tõrge kommentaari salvestamisel: ' + JSON.stringify(res);
                        });

                    } catch (error) {
                        console.error('Error:', error);
                    }
                },

                saveUserDoneCriteria(criterion) {
                    ajax('api/assignments/saveUserDoneCriteria', {criterionId: criterion.criterionId, done: criterion.done},
                        () => criterion.done
                            ? this.setCriterionState(criterion, 'criterion-done', 'Tingimus on salvestatud')
                            : this.setCriterionState(criterion, '', ''),
                        (res) => {
                            this.setCriterionState(criterion, 'bg-warning', '⚠️ Tõrge tingimuse salvestamisel: ' + JSON.stringify(res));
                            criterion.unsaved = true;
                            criterion.done = !criterion.done;
                        }
                    );
                },

                async submitSolution() {
                    await axios.post('/assignments/saveStudentSolutionUrl', {
                        studentId: this.userId,
                        teacherId: this.assignment.teacherId,
                        assignmentId: this.assignment.assignmentId,
                        solutionUrl: this.solutionUrl,
                        criteria: this.criteria,
                        comment: this.commentText,
                    });
                },

                setCriterionState(criterion, className, tooltipText) {
                    this.$set(criterion, 'class', className);
                    this.$set(criterion, 'tooltipText', tooltipText);
                }
            },
        })
    ;
</script>
