<style>
    [v-cloak] {
        display: none;
    }

    h2 {
        margin-top: 10px;
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
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }

    .comment-section-container,
    .comment-section-empty {
        padding: 1rem;
    }

    .comment-entry {
        margin-bottom: 1rem;
    }

    .comment-entry li {
        margin-left: 0;
        padding-left: 0;


    }
    ul.list-group.list-group-criteria li:last-child {
        border-radius: 0 0 4px 4px;
    }

    ul.list-group.list-group-criteria li {
        border-top: 1px solid #d5d9dc;
        border-left: 0;
        border-right: 0;
        border-bottom: 0;
    }

    .comment-section {
        border-top: 1px solid #d5d9dc;
        border-radius: 0;
    }

    .comment-entry .card {
        border-radius: 0;
        border: 0 solid #d5d9dc;
        border-bottom-width: 1px;
    }

    /* Add a shadow to the .card */
    .card {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    ul.list-group.list-group-criteria li:last-child {
        border-radius: 0 0 4px 4px;
    }

    ul.list-group.list-group-criteria li:first-child {
        border-top: 0;
    }

    #solutionUrl {
        border-radius: 0 0 4px 4px;
        border-bottom: 0;
        border-left: 0;
        border-right: 0;
        padding-left: 15px
    }

    #submitSolutionButton {
        border-radius: 0 0 4px 0;
    }

    #commentText {
        border-radius: 0 0 0 4px;
        border-left: 0;
        border-right: 0;
        padding-left: 16px;
    }

    #commentText.is-invalid {
        margin-top: 15px !important;
        border-radius: 0 !important;
        border: 1px solid #dc3545;
        margin-left: 15px;

    }

    .gray-background {
        background-color: #f8f9fa;
    }

    .card-header {
        /* Linear gradient suble grayish blue */
        background: linear-gradient(180deg, rgba(193, 193, 193, 0.0) 0%, rgba(193, 193, 193, 0.3) 100%);
    }

    #commentUnsavedReason {
        margin-left: 15px;
        margin-bottom: 15px;
    }

    #submitCommentButton.is-invalid {
        margin-top: 15px !important;
        margin-right: 15px !important;
        border-radius: 0 4px 4px 0 !important;
    }

    .comment-entry .card td, .comment-entry .card th {
        border: 1px solid gray;
    }

    .comment-entry .card th {
        background-color: #78787833


    }

    .comments-container {
        max-height: 400px;
        overflow-y: auto;
        border-bottom: 1px solid #d5d9dc;
    }

</style>

<div id="app" class="container mt-5" v-cloak>


    <h1><strong>{{ assignment.assignmentName }}</strong></h1>
    <!-- Instruction and Solving Cards -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">

            <!-- Instructions -->
            <div class="card mb-4">
                <div class="card-header" v-tooltip="tooltips.criteria">
                    <h2 class="card-title d-flex justify-content-between align-items-center">
                        <strong>Juhend</strong> <i class="fa fa-info-circle"></i>
                    </h2>
                </div>
                <div class="card-body">
                    <p v-html="renderMarkdown(assignment.assignmentInstructions)"></p>
                </div>
            </div>

            <!-- Solving -->
            <div class="card mb-4">
                <div class="card-header" v-tooltip="tooltips.criteria">
                    <h2 class="card-title d-flex justify-content-between align-items-center">
                        <strong>Lahendamine</strong> <i class="fa fa-puzzle-piece"></i>
                    </h2>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-criteria">
                        <li v-for="(criterion, index) in criteria"
                            :key="criterion.criterionId"
                            class="list-group-item"
                            :class="criterion.done ? 'criterion-done' : ''">
                            <label class="d-flex align-items-center">
                                <input type="checkbox"
                                       class="form-check-input me-2"
                                       v-model="criterion.done"
                                       @change="saveUserDoneCriteria(criterion)"/>
                                <span class="me-2">{{ index + 1 }}.</span>
                                <span>{{ criterion.description }}</span>
                                <span v-if="criterion.unsaved"
                                      class="text-warning ms-2"
                                      v-tooltip="criterion.tooltipText">&#9888;</span>
                            </label>
                        </li>
                        <div class="input-group" v-if="canSubmitSolution">
                            <input type="url"
                                   id="solutionUrl"
                                   class="form-control"
                                   v-model="solutionUrl"
                                   placeholder="Sisesta lahenduse URL"
                                   required>
                            <button type="submit"
                                    id="submitSolutionButton"
                                    class="btn btn-success"
                                    :disabled="!canSubmitSolution"
                                    @click="submitSolution">
                                Esita
                            </button>
                        </div>
                    </ul>
                </div>
            </div>

            <!-- Comments -->
            <div class="card mb-4">
                <div class="card-header" v-tooltip="tooltips.comments">
                    <h2 class="card-title d-flex justify-content-between align-items-center">
                        <strong>Vestlus</strong> <i class="fa fa-comment"></i>
                    </h2>
                </div>
                <div class="card-body p-0">
                    <div class="comments-container" ref="commentsContainer">
                        <div v-if="comments.length > 0" class="comment-section-container gray-background p-0">
                            <div v-for="comment in comments" class="comment-entry mb-0">
                                <div class="card">
                                    <div class="card-body">
                                        <p>
                                            {{ comment.createdAt }}
                                            <strong>{{ comment.name || 'Tundmatu' }}</strong><br>
                                            <span v-html="renderMarkdown(comment.comment)"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="comment-section-empty gray-background">
                            <p>Sõnumeid pole.</p>
                        </div>
                    </div>
                    <div id="commentSection" class="d-flex flex-column">
                        <div class="input-group">
                            <textarea id="commentText"
                                      class="form-control border-end-0"
                                      :class="{ 'is-invalid': commentUnsaved }"
                                      rows="3"
                                      placeholder="Lisa sõnum siia..."
                                      v-model="commentText"
                                      required></textarea>
                            <button type="button"
                                    id="submitCommentButton"
                                    class="btn btn-success"
                                    :class="{ 'is-invalid': commentUnsaved }"
                                    :disabled="!commentText.trim()"
                                    style="border-radius: 0 0 4px 0;"
                                    @click="submitComment">
                                Esita
                            </button>
                        </div>
                        <div v-if="commentUnsaved" id="commentUnsavedReason" class="text-danger mt-2">
                            {{ commentUnsavedReason }}
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


<script>
    const assignmentData = <?= json_encode($assignment); ?>;
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
            tooltips: {
                instructions: 'Loe juhendit ja lahenda ülesanne vastavalt.',
                criteria: 'Loe loetelu ja märgi, kui tingimus on täidetud.',
                comments: 'Küsi, arutle, jaga mõtteid õpetajaga.',
            },
            assignment: assignmentData,
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

            this.criteria = assignmentCriteria.map(criterion => ({
                criterionId: criterion.criterionId,
                description: criterion.criterionName,
                done: userDoneCriteria[criterion.criterionId]?.completed || false,
                unsaved: false
            }));

            this.solutionUrl = studentData.solutionUrl || '';
            this.comments = studentData.comments || [];
        },
        computed: {
            canSubmitSolution() {
                return this.criteria.every(c => c.done);
            },
            renderMarkdown() {
                return text => window.marked.parse(text || '');
            }
        },
        methods: {
            saveUserDoneCriteria(criterion) {
                ajax('api/assignments/saveUserDoneCriteria', {
                    criterionId: criterion.criterionId,
                    done: criterion.done
                }, () => {
                    criterion.tooltipText = '';
                    criterion.unsaved = false;
                }, err => {
                    criterion.unsaved = true;
                    criterion.done = !criterion.done; // revert
                    criterion.tooltipText = '⚠️ Viga salvestamisel: ' + JSON.stringify(err);
                });
            },

            submitSolution() {
                ajax('api/assignments/submitSolution', {
                    assignmentId: this.assignment.assignmentId,
                    solutionUrl: this.solutionUrl
                }, () => {
                    // success handling if needed
                }, err => {
                    // error handling if needed
                });
            },
            scrollToBottom() {
                if (this.$refs.commentsContainer) {
                    this.$nextTick(() => {
                        const container = this.$refs.commentsContainer;
                        container.scrollTop = container.scrollHeight;
                    });
                }
            },
            submitComment() {
                ajax('api/assignments/addComment', {
                    assignmentId: this.assignment.assignmentId,
                    comment: this.commentText,
                }, () => {
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
                        comment: this.commentText
                    });
                    this.commentText = '';
                    this.scrollToBottom();
                }, res => {
                    this.commentUnsaved = true;
                    this.commentUnsavedReason = '⚠️ Tõrge kommentaari salvestamisel: ' + JSON.stringify(res);
                    // Scroll to the bottom of the page after 100ms to make the error visible
                    setTimeout(() => {
                        window.scrollTo(0, document.body.scrollHeight);
                    }, 100);
                });
            }
        },
        mounted() {
            this.scrollToBottom();
        }
    });
</script>
