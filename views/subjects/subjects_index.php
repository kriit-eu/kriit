<style>
    #subject-table th {
        background-color: #f2f2f2;
    }

    .red-cell {
        background-color: rgb(255, 180, 176) !important;
    }

    .yellow-cell {
        background-color: #fff8b3 !important;
    }

    .text-center {
        text-align: center;
    }

    [v-cloak] {
        display: none;
    }

    .student-header {
        cursor: help;
        border-bottom: 1px dashed #999;
        font-weight: normal;
        font-family: Arial Narrow, sans-serif;
        font-size: 0.9em;
        max-width: 40px;
        width: 40px;
        padding: 1px !important;
        text-align: center;
        vertical-align: middle;
    }

    .student-firstname {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        font-weight: normal;
    }

    .student-lastname {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        font-weight: bold;
    }

    .toggle-icon {
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .toggle-icon.collapsed {
        transform: rotate(-90deg);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .criterion-done {
        background-color: #e8f5e9 !important;
        transition: background-color 0.3s ease;
        color: #666666;
    }

    .criterion-done label {
        color: #666666;
    }

    .criterion-done .form-check-input:checked {
        opacity: 0.7;
    }

    .list-group-item {
        transition: background-color 0.3s ease;
    }

    .teacher-checkbox {
        cursor: pointer;
    }

    .student-checkbox {
        cursor: not-allowed;
    }

    .comment-proposed-solution {
        background-color: #fff8b3 !important;
    }

    .comment-proposed-solution .card {
        background-color: transparent;
    }

    .comments-container {
        max-height: 400px;
        overflow-y: auto;
        border-bottom: 1px solid #d5d9dc;
    }

    .comment-section-container {
        background-color: #f8f9fa;
    }

    .comment-entry {
        border-bottom: 1px solid #d5d9dc;
    }

    .comment-entry .card {
        border: none;
        background: transparent;
    }

    .comment-entry .card-body {
        padding: 1rem;
    }

    .comment-section-empty {
        padding: 1rem;
        background-color: #f8f9fa;
    }

    .comment-proposed-solution .card {
        background-color: transparent;
    }

    .comment-actions {
        display: flex;
        justify-content: flex-end;
    }

    .text-center[data-bs-toggle="tooltip"] {
        cursor: pointer;
    }

    td[data-bs-toggle="tooltip"] {
        cursor: pointer;
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>
<br>
<br>
<script src="https://unpkg.com/vue@3/dist/vue.global<?= ENV === ENV_PRODUCTION ? '.prod' : '' ?>.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<div id="app" v-cloak>
    <div class="col text-end mb-3" v-if="isAdmin">
        <button class="btn btn-primary" @click="goToAdmin">Muuda</button>
    </div>

    <pre>{{ isStudent }}</pre>

    <div class="row">
        <template v-for="group in Object.values(groups)">

            <div class="table-responsive">
                <table id="subject-table" class="table table-bordered">
                    <thead>
                    <tr>
                        <th>{{ group.groupName }}</th>
                        <th v-if="isStudent">Staatus / Hinne</th>
                        <template v-if="!isStudent">
                            <th v-for="student in getSortedStudents(group.students)"
                                :key="student.userId"
                                data-bs-toggle="tooltip"
                                :title="student.userName"
                                class="student-header">
                                <template v-if="getSortedStudents(group.students).length === 1">
                                    Seis
                                </template>
                                <template v-else>
                                    <span class="student-firstname">{{ student.userName.split(' ')[0].substring(0, 4) }}</span>
                                    <span class="student-lastname">{{ student.userName.split(' ').slice(-1)[0].substring(0, 4) }}</span>
                                </template>
                        </template>
                    </tr>
                    </thead>
                    <tbody>
                    <template v-for="subject in Object.values(group.subjects)">
                        <tr>
                            <td :colspan="isStudent ? 2 : Object.keys(group.students).length + 1">
                                <b>{{ subject.subjectName }}</b>
                                <span class="text-muted ms-2">{{ subject.teacherName }}</span>
                            </td>
                        </tr>
                        <template v-if="subject.assignments && Object.keys(subject.assignments).length">
                            <tr v-for="assignment in getSortedAssignments(subject.assignments)">
                                <td>
                                        <span class="badge rounded-pill text-bg-primary"
                                              :class="getBadgeClass(assignment)"
                                              :data-days-remaining="assignment.daysRemaining">
                                            {{ formatDate(assignment.assignmentDueAt) }}
                                        </span>
                                    {{ assignment.assignmentName }}
                                </td>
                                
                                    <td v-for="student in getSortedStudents(group.students)"
                                        :key="student.userId"
                                        class="text-center"
                                        :class="'cursor-pointer'"
                                        data-bs-toggle="tooltip"
                                        :title="getStudentProgress(assignment.studentProgress[student.userId], assignment).tooltip"
                                        @click="handleAssignmentClick(assignment, student)">
                                        {{ getStudentProgress(assignment.studentProgress[student.userId], assignment).symbol }}
                                    </td>
                            </tr>
                        </template>
                        <tr>
                            <td :colspan="isStudent ? 2 : Object.keys(group.students).length + 2">&nbsp;</td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>
        </template>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="assignmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ currentAssignment?.assignmentName }} - {{ currentStudent?.userName
                        }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" v-if="currentAssignment">
                    <div class="container">
                        <!-- Instructions -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Juhend</h5>
                                <span class="toggle-icon" 
                                      :class="{ 'collapsed': !instructionsVisible }"
                                      @click="toggleInstructions">
                                    ��
                                </span>
                            </div>
                            <div class="card-body" v-show="instructionsVisible">
                                <p v-html="renderMarkdown(currentAssignment.assignmentInstructions)"></p>
                            </div>
                        </div>

                        <!-- Criteria -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">Kriteeriumid</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    <li v-for="(criterion, index) in currentCriteria"
                                        :key="criterion.criterionId"
                                        class="list-group-item"
                                        :class="criterion.done ? 'criterion-done' : ''">
                                        <label class="d-flex align-items-center">
                                            <input type="checkbox"
                                                   class="form-check-input me-2"
                                                   :class="isTeacher ? 'teacher-checkbox' : 'student-checkbox'"
                                                   v-model="criterion.done"
                                                   @change="saveUserDoneCriteria(criterion)"
                                                   :title="isTeacher ? null : 'Ainult õpetaja saab kriteeriumit eemaldada'"/>
                                            <span class="me-2">{{ index + 1 }}.</span>
                                            <span>{{ criterion.criterionName }}</span>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Comments -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Kommentaarid</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="comments-container" ref="commentsContainer">
                                    <div v-if="currentComments.length" class="comment-section-container">
                                        <div v-for="comment in currentComments" 
                                             class="comment-entry"
                                             :class="{ 'comment-proposed-solution': comment.assignmentCommentTypeId === 2 }">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <strong>{{ comment.name }}</strong>
                                                            <small class="text-muted">{{ comment.createdAt }}</small>
                                                        </div>
                                                        <!-- Miniature grade buttons for proposed solutions -->
                                                        <div v-if="comment.assignmentCommentTypeId === 2" class="d-flex gap-1">
                                                            <button v-for="grade in ['MA', '1', '2', '3', '4', '5', 'A']"
                                                                    :key="grade"
                                                                    class="btn btn-sm flex-grow-0"
                                                                    :class="{
                                                                        'btn-outline-danger': ['MA', '1', '2'].includes(grade) && selectedGrade !== grade,
                                                                        'btn-outline-success': ['3', '4', '5', 'A'].includes(grade) && selectedGrade !== grade,
                                                                        'btn-danger active': ['MA', '1', '2'].includes(grade) && selectedGrade === grade,
                                                                        'btn-success active': ['3', '4', '5', 'A'].includes(grade) && selectedGrade === grade
                                                                    }"
                                                                    style="min-width: 32px; padding: 0.1rem 0.3rem;"
                                                                    @click="selectGrade(grade)">
                                                                {{ grade }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <p v-html="renderMarkdown(comment.comment)"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="comment-section-empty">
                                        <p>Kommentaare pole.</p>
                                    </div>
                                </div>
                                <div class="mt-3 p-3">
                                    <!-- Grade Buttons -->
                                    <div class="d-flex gap-2 mb-3">
                                        <button v-for="grade in ['MA', '1', '2', '3', '4', '5', 'A']"
                                                :key="grade"
                                                class="btn flex-grow-1"
                                                :class="{
                                                    'btn-outline-danger': ['MA', '1', '2'].includes(grade) && selectedGrade !== grade,
                                                    'btn-outline-success': ['3', '4', '5', 'A'].includes(grade) && selectedGrade !== grade,
                                                    'btn-danger active': ['MA', '1', '2'].includes(grade) && selectedGrade === grade,
                                                    'btn-success active': ['3', '4', '5', 'A'].includes(grade) && selectedGrade === grade
                                                }"
                                                @click="selectGrade(grade)">
                                            {{ grade }}
                                        </button>
                                    </div>

                                    <textarea v-model="commentText"
                                              class="form-control mb-2"
                                              rows="3"
                                              placeholder="Lisa kommentaar..."></textarea>
                                    <div class="comment-actions">
                                        <button class="btn btn-primary"
                                                @click="submitComment"
                                                :disabled="!commentText.trim() && !selectedGrade">
                                            Saada
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const app = Vue.createApp({
        data() {
            return {
                groups: <?= json_encode($groups) ?>,
                isAdmin: <?= json_encode($this->auth->userIsAdmin) ?>,
                currentAssignment: null,
                currentStudent: null,
                currentCriteria: [],
                currentComments: [],
                commentText: '',
                modal: null,
                instructionsVisible: false,
                isTeacher: <?= json_encode($this->auth->userIsTeacher) ?>,
                selectedGrade: null,
                gradingFeedback: '',
            }
        },
        computed: {
            isStudent() {
                // Kontrolli kas on ainult üks grupp ja selles üks õpilane
                const allGroups = Object.values(this.groups);
                console.log('Groups:', allGroups); // Debug info
                
                if (allGroups.length === 1) {
                    const students = Object.values(allGroups[0].students || {});
                    console.log('Students in group:', students); // Debug info
                    console.log('Is student view:', students.length === 1); // Debug info
                    return students.length === 1;
                }
                return false;
            },
            getStudentProgress() {
                return (progress, assignment) => {
                    console.log('Progress:', progress); // Debug info
                    console.log('Assignment:', assignment); // Debug info
                    
                    if (!progress) return { symbol: '', tooltip: '' };
                    
                    if (progress.grade) {
                        if (progress.grade === 'A') {
                            return { 
                                symbol: progress.grade,
                                tooltip: 'Arvestatud'
                            };
                        } else if (progress.grade === 'MA') {
                            return { 
                                symbol: progress.grade,
                                tooltip: 'Mittearvestatud'
                            };
                        } else {
                            return { 
                                symbol: progress.grade,
                                tooltip: `Hinne: ${progress.grade}`
                            };
                        }
                    }

                    if (progress.assignmentStatusId === '2') {
                        return { 
                            symbol: '⌛',
                            tooltip: 'Ülesanne on kontrollimisel'
                        };
                    }

                    const today = new Date();
                    const dueDate = assignment.assignmentDueAt ? new Date(assignment.assignmentDueAt) : null;
                    
                    if (dueDate && (dueDate <= today) && progress.assignmentStatusId === '1') {
                        return { 
                            symbol: '❌',
                            tooltip: 'Tähtaeg möödas, ülesanne esitamata'
                        };
                    }

                    if (progress.assignmentStatusId === '1') {
                        return { 
                            symbol: '',
                            tooltip: 'Ülesanne esitamata'
                        };
                    }

                    return { symbol: '', tooltip: 'Ootel' };
                };
            }
        },
        methods: {
            goToAdmin() {
                window.location.href = 'admin/subjects';
            },
            formatDate(date) {
                return date ? new Date(date).toLocaleDateString('et-EE') : "Pole määratud";
            },
            getBadgeClass(assignment) {
                // Lisa siia olemasolev badge'i loogika
                return assignment.badgeClass;
            },
            openAssignmentModal(assignment, student) {
                
                if (!assignment.assignmentId || !student.userId) {
                    console.error('Missing required IDs:', { 
                        assignmentId: assignment.assignmentId, 
                        userId: student.userId 
                    });
                    return;
                }

                ajax(`assignments/${assignment.assignmentId}/${student.userId}`, null, (res) => {
                    const data = res.data;
                    this.currentAssignment = data;
                    this.currentStudent = student;
                    this.currentCriteria = Object.values(data.criteria || {}).map(criterion => ({
                        ...criterion,
                        done: criterion.done === 1
                    }));
                    this.currentComments = (data.comments || []).map(comment => ({
                        name: comment.userName || data.studentName,
                        createdAt: comment.assignmentCommentCreatedAt,
                        comment: comment.assignmentCommentText,
                        assignmentCommentTypeId: comment.assignmentCommentTypeId
                    }));

                    this.selectedGrade = data.grade || null;
                    this.gradingFeedback = '';

                    this.modal.show();
                }, (error) => {
                    console.error('Error loading assignment details:', error);
                });
            },
            renderMarkdown(text) {
                return marked.parse(text || '');
            },
            saveUserDoneCriteria(criterion) {
                // Kui kasutaja pole õpetaja ja üritab märget eemaldada, siis ei luba seda
                if (!this.isTeacher && !criterion.done) {
                    criterion.done = true;
                    return;
                }

                ajax('api/assignments/saveUserDoneCriteria', {
                    criterionId: criterion.criterionId,
                    done: criterion.done,
                    studentId: this.currentStudent.userId
                }, () => {
                    criterion.tooltipText = '';
                    criterion.unsaved = false;
                }, err => {
                    criterion.unsaved = true;
                    criterion.done = !criterion.done;
                    criterion.tooltipText = '⚠️ Viga salvestamisel: ' + JSON.stringify(err);
                });
            },
            submitComment() {
                if (!this.commentText.trim()) return;

                ajax('api/assignments/addComment', {
                    assignmentId: this.currentAssignment.assignmentId,
                    comment: this.commentText,
                    studentId: this.currentStudent.userId
                }, () => {
                    this.currentComments.push({
                        name: this.currentStudent.userName,
                        createdAt: new Date().toLocaleString('et-EE'),
                        comment: this.commentText,
                        assignmentCommentTypeId: 1
                    });
                    this.commentText = '';
                    this.scrollToBottom();
                }, err => {
                    console.error('Error submitting comment:', err);
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
            formatStudentName(fullName) {
                const names = fullName.split(' ');
                if (names.length >= 2) {
                    return names[0].substring(0, 2) + names[1].substring(0, 2);
                }
                return fullName.substring(0, 4);
            },
            getSortedStudents(students) {
                return Object.values(students)
                    .sort((a, b) => a.userName.localeCompare(b.userName, 'et'));
            },
            getSortedAssignments(assignments) {
                return Object.values(assignments)
                    .sort((a, b) => {
                        if (a.assignmentDueAt && b.assignmentDueAt) {
                            return new Date(a.assignmentDueAt) - new Date(b.assignmentDueAt);
                        }
                        return a.assignmentName.localeCompare(b.assignmentName, 'et');
                    });
            },
            handleAssignmentClick(assignment, student) {
                console.log('Handling click:', { assignment, student }); // Debug info
                if (this.isStudent) {
                    window.location.href = `assignments/${assignment.assignmentId}`;
                } else {
                    this.openAssignmentModal(assignment, student);
                }
            },
            toggleInstructions() {
                this.instructionsVisible = !this.instructionsVisible;
            },
            selectGrade(grade) {
                this.selectedGrade = this.selectedGrade === grade ? null : grade;
            },
            saveGrade() {
                if (!this.selectedGrade) return;
                
                ajax('api/assignments/grade', {
                    assignmentId: this.currentAssignment.assignmentId,
                    studentId: this.currentStudent.userId,
                    grade: this.selectedGrade,
                    feedback: this.gradingFeedback
                }, () => {
                    // Värskenda UI-d pärast edukat salvestamist
                    this.currentAssignment.grade = this.selectedGrade;
                    if (this.gradingFeedback) {
                        this.currentComments.push({
                            name: 'Süsteem',
                            createdAt: new Date().toLocaleString('et-EE'),
                            comment: `Hinne: ${this.selectedGrade}\n\n${this.gradingFeedback}`,
                            assignmentCommentTypeId: 1
                        });
                    }
                    this.scrollToBottom();
                }, err => {
                    console.error('Error saving grade:', err);
                });
            },
        },
        mounted() {
            this.modal = new bootstrap.Modal(document.getElementById('assignmentModal'));
            this.modal._element.addEventListener('shown.bs.modal', () => {
                this.scrollToBottom();
            });

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }
    });

    app.mount('#app');
</script>
