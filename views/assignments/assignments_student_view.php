<style>
    [v-cloak] {
        display: none;
    }

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
    }

    .criterion-unsaved {
        background-color: #f2dede;
        color: #a94442;
        text-decoration: line-through;
    }

    #commentText.form-control:focus {
        border-color: #80bdff;
        box-shadow: inset 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }

    .list-group-criteria {
        margin: 0 -15px -15px;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }

    .comment-section-container,
    .comment-section-empty {
        padding: 17px;
    }

    .comment-entry {
        margin-bottom: 1rem;
    }

    .m--1 {
        margin: -1px;
    }
</style>

<div id="app" class="container mt-5" v-cloak>
    <div class="row">
        <h1><strong>{{ assignment.assignmentName }}</strong></h1>

        <!-- Instruction and Solving Cards -->
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <!-- Instructions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h2 class="card-title d-flex justify-content-between align-items-center">
                            <strong>Juhend</strong> <i class="fa fa-info-circle"></i>
                        </h2>
                    </div>
                    <div class="card-body">
                        <p v-html="renderedInstructions"></p>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12">
                <!-- Solving -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h2 class="card-title d-flex justify-content-between align-items-center">
                            <strong>Lahendamine</strong> <i class="fa fa-puzzle-piece"></i>
                        </h2>
                    </div>
                    <div class="card-body">
                        <p>Loe järgmist loetelu ja märgi iga sammu juurde linnuke kohe, kui oled selle täitnud.</p>
                        <ul class="list-group list-group-criteria">
                            <li v-for="(criterion, index) in criteria"
                                :key="criterion.criterionId"
                                class="list-group-item"
                                :class="criterion.done ? 'criterion-done' : ''"
                                data-bs-toggle="tooltip">
                                <label class="d-flex">
                                    <div class="flex-shrink-0">
                                        <input type="checkbox"
                                               class="form-check-input me-2"
                                               v-model="criterion.done"
                                               @change="saveUserDoneCriteria(criterion)"/>
                                        <span class="me-2">{{ index + 1 }}.</span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span v-if="criterion.unsaved"
                                              class="text-warning ms-2"
                                              data-bs-toggle="tooltip"
                                              v-tooltip="criterion.tooltipText">&#9888;</span>
                                        {{ criterion.description }}
                                    </div>
                                </label>
                            </li>
                            <div class="input-group mt-3" v-if="canSubmitSolution">
                                <input type="url"
                                       id="solutionUrl"
                                       class="form-control"
                                       v-model="solutionUrl"
                                       placeholder="Enter solution URL"
                                       required>
                                <button type="submit"
                                        class="btn btn-success"
                                        :disabled="!canSubmitSolution"
                                        @click="submitSolution">
                                    Esita
                                </button>
                            </div>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title d-flex justify-content-between align-items-center">
                            <strong>Vestlus</strong><i class="fa fa-comment"></i>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div v-if="comments.length > 0" class="comment-section-container">
                            <div v-for="comment in comments" class="comment-entry">
                                <div class="card">
                                    <div class="card-body">
                                        <p>
                                            {{ comment.createdAt }}
                                            <strong>{{ comment.name || 'Tundmatu' }}</strong><br>
                                            <em>{{ comment.comment }}</em>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="comment-section-empty">
                            <p>Sõnumeid pole.</p>
                        </div>
                        <div id="commentSection" class="d-flex m--1">
                            <div class="input-group">
                                <textarea id="commentText"
                                          class="form-control border-end-0"
                                          :class="commentUnsaved ? 'is-invalid' : ''"
                                          rows="3"
                                          placeholder="Lisa sõnum siia..."
                                          v-model="commentText"
                                          style="border-radius: 0 0 0 4px;"
                                          required></textarea>
                                <button type="button"
                                        class="btn btn-success"
                                        :disabled="!commentText.trim()"
                                        style="border-radius: 0 0 4px 0;"
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
</div>


<!-- Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<?php if (ENV == ENV_PRODUCTION): ?>
    <script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
<?php else: ?>
    <script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    const assignment = <?= json_encode($assignment); ?>;
    const userId = <?= json_encode($this->auth->userId); ?>;

    Vue.directive('tooltip', {
        inserted(el, binding) {
            new bootstrap.Tooltip(el, {title: binding.value, placement: 'top'});
        },
        update(el, binding) {
            el.setAttribute('data-bs-original-title', binding.value);
        }
    });

    new Vue({
        el: '#app',
        data: {
            assignment: assignment,
            criteria: [],
            solutionUrl: '',
            userId: userId,
            commentText: '',
            commentUnsaved: false,
            commentUnsavedReason: '',
            comments: []
        },
        created() {
            const assignmentCriteria = Object.values(this.assignment.criteria || {});
            const studentData = Object.values(this.assignment.students || {})[0] || {};
            const userDoneCriteria = studentData.userDoneCriteria || {};

            this.criteria = assignmentCriteria.map(criterion => {
                const done = userDoneCriteria[criterion.criterionId]?.completed || false;
                return {
                    criterionId: criterion.criterionId,
                    description: criterion.criterionName,
                    done: done,
                    tooltipText: done ? 'Tingimus on salvestatud' : ''
                };
            });

            this.solutionUrl = studentData.solutionUrl || '';
            this.comments = studentData.comments || [];
        },
        computed: {
            canSubmitSolution() {
                return this.criteria.every(c => c.done);
            },
            renderedInstructions() {
                return marked.parse(this.assignment.assignmentInstructions || '');
            }
        },
        methods: {
            submitComment() {
                ajax('api/assignments/addComment', {
                    assignmentId: this.assignment.assignmentId,
                    comment: this.commentText,
                }, res => {
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
                }, res => {
                    this.commentUnsaved = true;
                    this.commentUnsavedReason = '⚠️ Tõrge kommentaari salvestamisel: ' + JSON.stringify(res);
                });
            },
            saveUserDoneCriteria(criterion) {
                ajax('api/assignments/saveUserDoneCriteria', {
                    criterionId: criterion.criterionId,
                    done: criterion.done
                }, () => {
                    criterion.tooltipText = criterion.done ? 'Tingimus on salvestatud' : '';
                    criterion.unsaved = false;
                }, res => {
                    criterion.unsaved = true;
                    criterion.done = !criterion.done;
                    criterion.tooltipText = '⚠️ Tõrge tingimuse salvestamisel: ' + JSON.stringify(res);
                });
            },
            async submitSolution() {
                ajax('api/assignments/submitSolution', {
                    assignmentId: this.assignment.assignmentId,
                    solutionUrl: this.newSolutionUrl,
                });
            }

        },
    });
</script>
