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
        padding-left: 15px;
        margin-right: 1px;
    }

    #solutionUrl:focus {
        border-color: #80bdff;
        box-shadow: inset 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        border-radius: 0 0 0 4px;
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
        /* Linear gradient subtle grayish blue */
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

    .comment-entry .card td,
    .comment-entry .card th {
        border: 1px solid gray;
    }

    .comment-entry .card th {
        background-color: #78787833;
    }

    .comments-container {
        max-height: 400px;
        overflow-y: auto;
        border-bottom: 1px solid #d5d9dc;
    }

    @media (max-width: 438px) {
        .container {
            padding: 4px;
        }
    }

    .pulsate {
        animation: pulsate 1s ease-in-out 10 forwards;
        color: #dc3545;
        font-weight: bold;
        text-shadow: 0 0 8px rgba(220, 53, 69, 0.6);
    }

    @keyframes pulsate {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.15);
        }
        100% {
            transform: scale(1);
        }
    }

    .slide-enter-from,
    .slide-leave-to {
        max-height: 0;
        opacity: 0;
    }

    .slide-enter-to,
    .slide-leave-from {
        max-height: 50px;
        opacity: 1;
    }

    .slide-enter-active,
    .slide-leave-active {
        transition: max-height 0.9s ease, opacity 0.9s ease;
        overflow: hidden;
    }

    .grade-badge-floating {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        filter: drop-shadow(0 0 6px rgba(0, 0, 0, 0.2));
    }


</style>

<div id="app" class="container mt-5" v-cloak>
    <div class="position-relative">
        <h1 class="d-flex align-items-center">

            <!-- Assignment name -->
            <strong class="me-auto">{{ assignment.assignmentName }}</strong>

            <!-- Grade badge -->
            <div class="grade-badge" ref="gradeBadge">
                <div v-if="isWaitingForReview" class="badge bg-warning text-black">
                    Kontrollimisel
                </div>
                <div v-else-if="assignment.grade"
                     :class="gradeClass"
                     v-tooltip="
                         assignment.grade === 'MA' ? 'Mittearvestatud. Paranda ja esita uuesti!'
                      : +assignment.grade  <  '3'  ? 'Paranda ja esita uuesti!'
                      :  assignment.grade === 'A'  ? 'Arvestatud' : null">
                    {{ assignment.grade }}
                </div>
                <div v-else :class="dueAtClass">
                    {{ assignment.assignmentDueAt }}
                </div>
            </div>
        </h1>
    </div>
    <br>

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
                        <transition name="slide">
                            <div class="input-group" v-if="criteria.every(c => c.done)">
                                <input type="url"
                                       id="solutionUrl"
                                       class="form-control"
                                       v-model="solutionUrl"
                                       :disabled="assignment.grade === 'A' || assignment.grade === '5'"
                                       placeholder="Sisesta lahenduse URL"
                                       required>
                                <div class="d-inline-block"
                                     v-tooltip="buttonTooltip">
                                    <button type="submit"
                                            id="submitSolutionButton"
                                            class="btn"
                                            :class="originalSolutionUrl ? 'btn-warning' : 'btn-success'"
                                            :disabled="!canSubmitSolution"
                                            v-show="isSolutionUrlChanged || (assignment.grade !== '5' && assignment.grade !== 'A' && assignment.assignmentStatusId === <?= ASSIGNMENT_STATUS_GRADED ?>)"
                                            @click="submitSolution">
                                        <template v-if="isSolutionUrlChanged">
                                            {{ originalSolutionUrl ? 'Muuda lahenduse URL-i' : 'Esita' }}
                                        </template>
                                        <template v-else>
                                            Esita sama URL lahendusena uuesti
                                        </template>
                                    </button>
                                </div>
                            </div>
                        </transition>
                    </ul>
                </div>
            </div>

            <!-- Comments -->
            <div class="card mb-4">
                <div class="card-header" v-tooltip="tooltips.comments">
                    <h2 class="card-title d-flex justify-content-between align-items-center">
                        <strong>Küsimused, vastused ja kommentaarid</strong> <i class="fa fa-comment"></i>
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
                                Saada
                            </button>
                        </div>
                        <transition name="slide">
                            <div v-if="commentUnsaved" id="commentUnsavedReason" class="text-danger mt-2">
                                {{ commentUnsavedReason }}
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<?php if (ENV == ENV_PRODUCTION): ?>
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<?php else: ?>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<?php endif; ?>

<script>
    const assignmentData = <?= json_encode($assignment); ?>;



    const initTooltip = (el, value) => {
        el._tooltip = new bootstrap.Tooltip(el, {
            title: value,
            placement: 'top',
            trigger: 'hover'
        });
    };

    const app = Vue.createApp({
        data() {
            return {
                tooltips: {
                    instructions: 'Loe juhendit ja lahenda ülesanne vastavalt.',
                    criteria: 'Loe loetelu ja märgi, kui tingimus on täidetud.',
                    comments: 'Küsi, arutle, jaga mõtteid õpetajaga.',
                },
                assignment: assignmentData,
                criteria: [],
                solutionUrl: '',
                originalSolutionUrl: '',
                commentText: '',
                comments: [],
                isSubmitting: false,
                commentUnsaved: false,
                commentUnsavedReason: ''
            }
        },
        created() {
            const assignmentCriteria = Object.values(this.assignment.criteria || {});
            
            this.criteria = assignmentCriteria.map(criterion => ({
                criterionId: criterion.criterionId,
                description: criterion.criterionName,
                done: criterion.done === 1,
                unsaved: false
            }));

            this.solutionUrl = this.assignment.solutionUrl || '';
            this.originalSolutionUrl = this.assignment.solutionUrl || '';
            this.comments = this.assignment.comments || [];
        },
        computed: {
            positiveGrade() {
                return this.assignment.grade && this.assignment.grade > 0;
            },
            gradeClass() {
                const grade = this.assignment.grade;
                const assignmentStatusId = this.assignment.assignmentStatusId;
                if (assignmentStatusId === <?= ASSIGNMENT_STATUS_WAITING_FOR_REVIEW ?>) {
                    return 'badge bg-warning text-white float-end';
                } else if (['1', '2', 'MA'].includes(grade)) {
                    return 'badge bg-danger text-white float-end';
                } else {
                    return 'badge bg-success text-white float-end';
                }
            },
            dueDate() {
                const [day, month, year] = this.assignment.assignmentDueAt.split('.');
                return new Date(year, month - 1, day); // month is 0-based in JS
            },
            isOneDayLeft() {
                const today = new Date();
                const diffTime = this.dueDate.getTime() - today.getTime();
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                return diffDays === 1;
            },
            isOverdue() {
                return !this.assignment.grade && this.dueDate < new Date();
            },
            isSolutionUrlChanged() {
                return this.solutionUrl !== this.originalSolutionUrl;
            },
            canSubmitSolution() {
                if (this.assignment.grade === 'A') {
                    return false;
                }
                if (this.isWaitingForReview && !this.isSolutionUrlChanged) {
                    return false;
                }
                return !this.isSubmitting;
            },
            renderMarkdown() {
                return text => window.marked.parse(text || '');
            },
            dueAtClass() {
                if (this.isOverdue) {
                    return 'badge bg-danger text-white float-end pulsate';
                } else if (this.isOneDayLeft) {
                    return 'badge bg-warning text-black float-end';
                } else {
                    return 'badge bg-secondary text-white float-end';
                }
            },
            isWaitingForReview() {
                return this.assignment.assignmentStatusId === <?= ASSIGNMENT_STATUS_WAITING_FOR_REVIEW ?>;
            },
            buttonTooltip() {
                if (this.assignment.grade === 'A' || +this.assignment.grade === 5) {
                    return 'Selle hindega ülesannet ei saa enam muuta';
                }
                if (this.isWaitingForReview) {
                    return 'Ülesanne on kontrollimisel';
                }
                if (this.canSubmitSolution) {
                    return 'Klõpsa lahenduse esitamiseks';
                }
                return 'Lahenduse salvestamine...';
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
                // Add confirmation check for grades 3 and 4
                if (['3', '4'].includes(this.assignment.grade)) {
                    if (!confirm('Sul on juba positiivne hinne. Kas oled kindel, et soovid lahendust uuesti esitada?')) {
                        return;
                    }
                }

                this.isSubmitting = true;
                ajax('api/assignments/submitSolution', {
                    assignmentId: this.assignment.assignmentId,
                    solutionUrl: this.solutionUrl
                }, () => {
                    this.originalSolutionUrl = this.solutionUrl;
                    this.isSubmitting = false;
                    this.assignment.assignmentStatusId = <?= ASSIGNMENT_STATUS_WAITING_FOR_REVIEW ?>;
                }, err => {
                    this.isSubmitting = false;
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
                    assignmentCommentText: this.commentText,
                    studentId: this.assignment.studentId
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
                        name: this.assignment.studentName,
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
            },
            handleScroll() {
                const scrolled = (window.scrollY || document.documentElement.scrollTop) > 100;
                this.$refs.gradeBadge.classList.toggle('grade-badge-floating', scrolled);
            }
        },
        mounted() {
            this.scrollToBottom();
            window.addEventListener('scroll', this.handleScroll);
        },
        unmounted() {
            window.removeEventListener('scroll', this.handleScroll);
        },
        directives: {
            tooltip: {
                mounted: (el, { value }) => {
                    value && initTooltip(el, value);
                },
                updated: (el, { value }) => {
                    if (!value) {
                        el._tooltip?.dispose();
                        el._tooltip = null;
                    } else {
                        el._tooltip
                            ? el.setAttribute('data-bs-original-title', value)
                            : initTooltip(el, value);
                    }
                },
                unmounted: el => el._tooltip?.dispose()
            }
        }
    });

    app.mount('#app');
</script>
