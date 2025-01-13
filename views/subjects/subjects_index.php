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
        position: relative;
        background-color: #f8f9fa;
    }

    .scroll-to-top {
        position: sticky;
        top: 10px;
        right: 10px;
        float: right;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 2;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }

    .scroll-to-top:hover {
        background-color: rgba(0, 0, 0, 0.7);
    }

    .comments-container.has-overflow .scroll-to-top {
        opacity: 1;
    }

    .comments-container::before {
        content: '';
        position: sticky;
        top: 0;
        left: 0;
        right: 0;
        height: 30px;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0) 100%);
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 2;
        display: block;
    }

    .comments-container.has-overflow::before {
        opacity: 1;
    }

    .comment-section-container {
        background-color: #f8f9fa;
        position: relative;
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

    .slide-enter-active,
    .slide-leave-active {
        transition: all 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        max-height: 2000px;
        overflow: hidden;
    }

    .slide-enter-from,
    .slide-leave-to {
        max-height: 0;
        opacity: 0;
        padding: 0;
    }

    .toggle-link {
        text-align: left;
        display: flex;
        align-items: center;
        gap: 4px;
        color: #6c757d;
        text-decoration: none;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: background-color 0.2s ease;
    }

    .toggle-link:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .edit-button {
        display: inline-flex;
        align-items: center;
        color: #6c757d;
        background: none;
        border: none;
        padding: 4px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        font-size: 16px;
    }

    .edit-button:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .instructions-content {
        margin-top: 10px;
    }

    .modal-content.grade-bg-danger .modal-body {
        background-color: #f8d7da;
    }

    .modal-content.grade-bg-success .modal-body {
        background-color: #e8f5e9;
    }

    .modal-content.grade-bg-none .modal-body {
        background-color: #ffffff;
    }

    .modal-header {
        background-color: #ffffff;
        border-top-left-radius: inherit;
        border-top-right-radius: inherit;
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
            <div class="modal-content" :class="getModalGradeClass">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span v-if="currentAssignment?.grade" class="badge me-2"
                              :class="{
                                  'bg-danger': ['MA', '1', '2'].includes(currentAssignment.grade),
                                  'bg-success': ['3', '4', '5', 'A'].includes(currentAssignment.grade)
                              }">
                            {{ currentAssignment.grade }}
                        </span>
                        {{ currentAssignment?.assignmentName }} - {{ currentStudent?.userName }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" v-if="currentAssignment">
                    <div class="container">
                        <!-- Instructions -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <h5 v-if="!editingTitle" style="margin: 0; overflow: hidden; text-overflow: ellipsis;">{{ currentAssignment.assignmentName }}</h5>
                                        <div v-else class="d-flex gap-2">
                                            <input type="text" 
                                                   class="form-control"
                                                   v-model="editedTitle"
                                                   @keyup.enter="saveTitle"
                                                   @keyup.esc="cancelEditingTitle"
                                                   ref="titleInput">
                                            <button class="btn btn-sm btn-success" @click="saveTitle">✓</button>
                                            <button class="btn btn-sm btn-secondary" @click="cancelEditingTitle">✕</button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <span class="toggle-link" 
                                              @click="toggleInstructions">
                                            {{ instructionsVisible ? 'Peida' : 'Näita' }} {{ instructionsVisible ? '▼' : '▶' }}
                                        </span>
                                        <button v-if="isTeacher && !editingTitle" 
                                                class="edit-button" 
                                                @click="startEditingTitle"
                                                title="Muuda pealkirja">
                                            ✎
                                        </button>
                                    </div>
                                </div>
                                <transition name="slide">
                                    <div v-if="instructionsVisible" class="instructions-content">
                                        <!-- sisu -->
                                    </div>
                                </transition>
                            </div>
                            <div class="card-body" v-show="instructionsVisible">
                                <div v-if="!editingInstructions">
                                    <div class="d-flex justify-content-end mb-2" v-if="isTeacher">
                                        <button class="btn btn-sm btn-link" 
                                                @click="startEditingInstructions"
                                                title="Muuda juhiseid">
                                            ✎ Muuda
                                        </button>
                                    </div>
                                    <p v-html="renderMarkdown(currentAssignment.assignmentInstructions)"></p>
                                </div>
                                <div v-else>
                                    <div class="d-flex gap-2 mb-2">
                                        <button class="btn btn-sm btn-success" @click="saveInstructions">Salvesta</button>
                                        <button class="btn btn-sm btn-secondary" @click="cancelEditingInstructions">Tühista</button>
                                    </div>
                                    <textarea class="form-control" 
                                              v-model="editedInstructions"
                                              rows="10"
                                              ref="instructionsInput"></textarea>
                                </div>
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
                                    <button class="scroll-to-top" 
                                            @click="scrollToTop" 
                                            title="Keri üles">
                                        ↑
                                    </button>
                                    <div v-if="currentComments.length" class="comment-section-container">
                                        <div v-for="comment in currentComments" 
                                             class="comment-entry"
                                             :class="{ 'comment-proposed-solution': comment.assignmentCommentIsProposedSolution }">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="d-flex align-items-baseline">
                                                            <strong class="text-body">{{ comment.assignmentCommentAuthorName }}</strong>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-3">
                                                            <!-- Miniature grade buttons for proposed solutions -->
                                                            <div v-if="comment.assignmentCommentIsProposedSolution" class="d-flex gap-1">
                                                                <button v-for="grade in getPossibleGrades(currentAssignment.subject)"
                                                                        :key="grade"
                                                                        class="btn btn-sm flex-grow-0"
                                                                        :class="{
                                                                            'btn-outline-danger': ['MA', '1', '2'].includes(grade) && comment.assignmentCommentGrade !== grade,
                                                                            'btn-outline-success': ['3', '4', '5', 'A'].includes(grade) && comment.assignmentCommentGrade !== grade,
                                                                            'btn-danger active': ['MA', '1', '2'].includes(grade) && comment.assignmentCommentGrade === grade,
                                                                            'btn-success active': ['3', '4', '5', 'A'].includes(grade) && comment.assignmentCommentGrade === grade
                                                                        }"
                                                                        style="min-width: 32px; padding: 0.1rem 0.3rem;"
                                                                        @click="handleGradeClick(comment, grade)">
                                                                    {{ grade }}
                                                                </button>
                                                            </div>
                                                            <small class="text-muted" style="margin-top: 2px;">{{ comment.assignmentCommentCreatedAt }}</small>
                                                        </div>
                                                    </div>
                                                    <p v-html="renderMarkdown(comment.assignmentCommentText)"></p>
                                                    
                                                    <!-- Tagasiside vorm -->
                                                    <div v-if="comment.showFeedbackForm" class="mt-3">
                                                        <textarea v-model="comment.feedback" 
                                                                  class="form-control mb-2" 
                                                                  placeholder="Lisa tagasiside..."></textarea>
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <button class="btn btn-secondary" 
                                                                    @click="comment.showFeedbackForm = false">
                                                                Tühista
                                                            </button>
                                                            <button class="btn btn-primary" 
                                                                    @click="submitGrade(comment)"
                                                                    :disabled="!comment.feedback">
                                                                Salvesta
                                                            </button>
                                                        </div>
                                                    </div>
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
                                        <button v-for="grade in getRegularGrades(currentAssignment.subject)"
                                                :key="grade || 'none'"
                                                class="btn flex-grow-1"
                                                :class="{
                                                    'btn-outline-danger': ['MA', '1', '2'].includes(grade) && selectedGrade !== grade,
                                                    'btn-outline-success': ['3', '4', '5'].includes(grade) && selectedGrade !== grade,
                                                    'btn-outline-secondary': grade === null && selectedGrade !== null,
                                                    'btn-danger active': ['MA', '1', '2'].includes(grade) && selectedGrade === grade,
                                                    'btn-success active': ['3', '4', '5'].includes(grade) && selectedGrade === grade,
                                                    'btn-secondary active': grade === null && selectedGrade === null
                                                }"
                                                @click="selectGrade(grade)">
                                            {{ grade || 'Hinne puudub' }}
                                        </button>
                                        
                                        <!-- A Grade Dropdown for binary grading -->
                                        <div class="dropdown" v-if="currentAssignment.subject.gradingSystem === 'non_numeric'">
                                            <button class="btn btn-outline-success dropdown-toggle flex-grow-1"
                                                    :class="{'active': selectedGrade === 'A'}"
                                                    type="button"
                                                    data-bs-toggle="dropdown"
                                                    data-bs-auto-close="true"
                                                    ref="aGradeDropdown">
                                                {{ selectedGrade === 'A' ? getSelectedAGradeDisplay() : 'A' }}
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li v-for="grade in ['A', 'A+', 'A++', 'A+++']">
                                                    <a class="dropdown-item" 
                                                       href="#" 
                                                       @click.prevent="selectGrade(grade); $refs.aGradeDropdown.click()"
                                                       :class="{'active': selectedGrade === 'A' && selectedAVariant === grade}">
                                                        {{ grade }}
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- 5+ option for numeric grading -->
                                        <button v-if="currentAssignment.subject.gradingSystem.startsWith('numeric')"
                                                class="btn btn-outline-success flex-grow-1"
                                                :class="{'active': selectedGrade === '5' && selectedAVariant === 'A+++'}"
                                                @click="selectGrade('5+')">
                                            5+
                                        </button>
                                    </div>

                                    <textarea v-model="commentText"
                                              class="form-control mb-2"
                                              rows="3"
                                              @input="handleCommentInput"
                                              :placeholder="selectedGrade ? 'Lisa tagasiside hindele (valikuline)...' : 'Lisa kommentaar...'"></textarea>
                                    <div class="comment-actions">
                                        <button class="btn btn-primary"
                                                @click="submitComment"
                                                :disabled="!commentText.trim() && (selectedGrade === undefined || selectedGrade === currentAssignment?.grade)">
                                            {{ getSaveButtonText }}
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
                commentTouched: false,
                modal: null,
                instructionsVisible: false,
                isTeacher: <?= json_encode($this->auth->userIsTeacher) ?>,
                selectedGrade: null,
                gradingFeedback: '',
                gradingSystems: {
                    'numeric_1_5': ['1', '2', '3', '4', '5'],
                    'numeric_2_5': ['2', '3', '4', '5'],
                    'non_numeric': ['MA', 'A']
                },
                feedbackTemplates: {
                    'positive': [
                        "✓ Hästi tehtud! Ülesanne on korrektselt lahendatud.",
                        "✓ Tubli töö! Kõik vajalik on olemas.",
                        "✓ Hea lahendus! Põhilised nõuded on täidetud.",
                        "✓ Korralik töö! Ülesanne on lahendatud nõuetekohaselt.",
                        "✓ Hästi! Kõik olulised osad on kaetud.",
                        "✓ Tubli! Ülesanne on lahendatud ootuspäraselt.",
                        "🌟 Suurepärane töö! Kõik kriteeriumid on täidetud väga heal tasemel. Jätka samas vaimus! 💪",
                        "✨ Väga tubli! Sinu lahendus näitab põhjalikku arusaamist teemast. 🎯",
                        "🎉 Hästi tehtud! Sinu töö on läbimõeldud ja korrektselt vormistatud. 👏",
                        "⭐ Muljetavaldav lahendus! Näed, et oled teemasse süvenenud ja andnud endast parima. 🏆"
                    ],
                    'excellent': [
                        "🌟 Väga hästi tehtud! Sinu lahendus on põhjalik ja läbimõeldud.",
                        "⭐ Suurepärane töö! Näitad head arusaamist teemast.",
                        "✨ Tubli! Sinu lahendus on kvaliteetne ja detailne.",
                        "🌟 Väga hea! Sinu töö näitab põhjalikku lähenemist.",
                        "⭐ Suurepärane! Lahendus on hästi struktureeritud ja selge.",
                        "✨ Väga tubli! Oled näidanud head analüütilist mõtlemist.",
                        "🌟 Hästi tehtud! Sinu töö on põhjalik ja läbimõeldud.",
                        "⭐ Tubli töö! Näitad head arusaamist ja püüdlikkust."
                    ],
                    'outstanding': [
                        "🏆 Suurepärane töö! Sinu lahendus on eeskujulik ja põhjalik! 🌟",
                        "🎯 Väga muljetavaldav! Oled näidanud sügavat arusaamist ja pühendumist! ⭐",
                        "💫 Oivaline sooritus! Sinu töö kvaliteet on silmapaistev! ✨",
                        "🏆 Väga tubli! Sinu lahendus on läbimõeldud ja innovaatiline! 🌟",
                        "🎯 Suurepärane! Oled näidanud erakordset tähelepanelikkust! ⭐",
                        "💫 Väga muljetavaldav töö! Sinu lähenemine on loominguline ja põhjalik! ✨",
                        "🏆 Oivaline! Sinu töö on detailne ja hästi argumenteeritud! 🌟",
                        "🎯 Suurepärane sooritus! Näitad sügavat arusaamist ja loovust! ⭐",
                        "💫 Väga tubli! Sinu lahendus on läbimõeldud ja professionaalne! ✨"
                    ],
                    'exceptional': [
                        "🌟 VÄGEV TÖÖ! Sinu lahendus on absoluutselt silmapaistev! Oled seadnud uue standardi! 🏆 ⭐",
                        "🎯 FANTASTILINE! Sinu töö on täiuslik näide eeskujulikust lahendusest! Imetlusväärne! 💫 🌟",
                        "✨ BRILJANTNE! Sinu pühendumus ja meisterlikkus on ülimalt muljetavaldavad! Oled end ületanud! 🌟 💪",
                        "🌟 SUUREPÄRANE! Sinu lahendus on geniaalne ja innovaatiline! Oled tõeline eeskuju! 🏆 ⭐",
                        "🎯 HÄMMASTAV! Sinu töö on täiuslik kombinatsioon loovusest ja täpsusest! Vau! 💫 🌟",
                        "✨ FENOMENAALNE! Sinu lähenemine on revolutsiooniline ja inspireeriv! Oled tõeline talent! 🌟 💪",
                        "🌟 MEISTERLIK! Sinu töö on absoluutne tippklass! Oled ületanud kõik ootused! 🏆 ⭐",
                        "🎯 VÕRRATU! Sinu lahendus on pööraselt hea! Oled seadnud uue kuldstandardi! 💫 🌟",
                        "✨ GENIAALNE! Sinu töö on täiesti erakordne! Oled näidanud tõelist meisterlikkust! 🌟 💪"
                    ]
                },
                selectedAVariant: null,
                editingTitle: false,
                editingInstructions: false,
                editedTitle: '',
                editedInstructions: '',
            }
        },
        computed: {
            isStudent() {
                const allGroups = Object.values(this.groups);

                if (allGroups.length === 1) {
                    const students = Object.values(allGroups[0].students || {});
                    return students.length === 1;
                }
                return false;
            },
            getStudentProgress() {
                return (progress, assignment) => {

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
            },
            getModalGradeClass() {
                const gradeToCheck = this.selectedGrade || this.currentAssignment?.grade;
                if (!gradeToCheck) return 'grade-bg-none';
                if (['MA', '1', '2'].includes(gradeToCheck)) return 'grade-bg-danger';
                if (['3', '4', '5', 'A'].includes(gradeToCheck)) return 'grade-bg-success';
                return 'grade-bg-none';
            },
            getSaveButtonText() {
                const hasComment = this.commentText.trim().length > 0;
                const gradeChanged = this.selectedGrade !== this.currentAssignment?.grade;
                
                if (gradeChanged && hasComment) return 'Salvesta kommentaar ja hinne';
                if (gradeChanged) return 'Salvesta hinne';
                return 'Salvesta kommentaar';
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
                return assignment.badgeClass;
            },
            openAssignmentModal(assignment, student) {
                ajax(`assignments/${assignment.assignmentId}/${student.userId}`, null, (res) => {
                    const data = res.data;
                    console.log(data);
                    
                    // Leiame õige grupi ja aine
                    let subject;
                    for (const group of Object.values(this.groups)) {
                        for (const [subjectId, subjectData] of Object.entries(group.subjects)) {
                            if (subjectData.assignments && subjectData.assignments[assignment.assignmentId]) {
                                subject = subjectData;
                                break;
                            }
                        }
                        if (subject) break;
                    }
                    
                    this.currentAssignment = {
                        ...data,
                        subject: subject
                    };
                    this.currentStudent = student;
                    this.currentCriteria = Object.values(data.criteria || {}).map(criterion => ({
                        ...criterion,
                        done: criterion.done === 1
                    }));
                    this.currentComments = data.comments || [];

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
                if (this.selectedGrade !== undefined) {
                    // If a grade is selected or being removed, save both grade and comment
                    const oldGrade = this.currentAssignment.grade;
                    const gradeChanged = oldGrade !== this.selectedGrade;

                    // If grade is changing but no feedback is provided, ask for confirmation
                    if (gradeChanged && !this.commentText.trim()) {
                        if (!confirm('Kas olete kindel, et soovite muuta hinnet ilma tagasisideta?')) {
                            return;
                        }
                    }

                    // Prepare the comment text for grade changes
                    let commentText = this.commentText;

                    if (gradeChanged) {
                        const getBadgeClass = grade => {
                            if (grade === null) return 'bg-secondary';
                            if (['MA', '1', '2'].includes(grade)) return 'bg-danger';
                            if (['3', '4', '5', 'A'].includes(grade)) return 'bg-success';
                            return 'bg-secondary';
                        };

                        const oldGradeBadge = `<span class="badge ${getBadgeClass(oldGrade)}">${oldGrade || 'Hinne puudub'}</span>`;
                        const newGradeBadge = `<span class="badge ${getBadgeClass(this.selectedGrade)}">${this.selectedGrade || 'Hinne puudub'}</span>`;
                        
                        const gradeText = `Hinne: ${oldGradeBadge} ➜ ${newGradeBadge}`;
                        commentText = this.commentText.trim()
                            ? `${gradeText}\n\n${this.commentText}`
                            : gradeText;
                    }

                    ajax('api/assignments/grade', {
                        assignmentId: this.currentAssignment.assignmentId,
                        studentId: this.currentStudent.userId,
                        grade: this.selectedGrade,
                        feedback: commentText
                    }, () => {
                        // Update the current assignment grade
                        this.currentAssignment.grade = this.selectedGrade;
                        
                        // Update the grid's studentProgress data
                        for (const group of Object.values(this.groups)) {
                            for (const subject of Object.values(group.subjects)) {
                                if (subject.assignments && subject.assignments[this.currentAssignment.assignmentId]) {
                                    if (!subject.assignments[this.currentAssignment.assignmentId].studentProgress) {
                                        subject.assignments[this.currentAssignment.assignmentId].studentProgress = {};
                                    }
                                    if (!subject.assignments[this.currentAssignment.assignmentId].studentProgress[this.currentStudent.userId]) {
                                        subject.assignments[this.currentAssignment.assignmentId].studentProgress[this.currentStudent.userId] = {};
                                    }
                                    subject.assignments[this.currentAssignment.assignmentId].studentProgress[this.currentStudent.userId].grade = this.selectedGrade;
                                    subject.assignments[this.currentAssignment.assignmentId].studentProgress[this.currentStudent.userId].assignmentStatusId = this.selectedGrade ? '3' : '2';
                                }
                            }
                        }
                        
                        // The grade endpoint already adds the comment, so we don't need to add it again
                        if (gradeChanged || this.commentText.trim()) {
                            // Refresh comments to show the new comment
                            ajax(`assignments/${this.currentAssignment.assignmentId}/${this.currentStudent.userId}`, null, (res) => {
                                this.currentComments = res.data.comments || [];
                                this.scrollToBottom();
                                this.modal.hide();
                            });
                        } else {
                            this.modal.hide();
                        }
                        
                        this.commentText = '';
                        this.selectedGrade = null;
                    }, err => {
                        console.error('Error saving grade:', err);
                    });
                } else if (this.commentText.trim()) {
                    // If no grade selected but has comment text, save as regular comment
                    ajax('api/assignments/addComment', {
                        assignmentId: this.currentAssignment.assignmentId,
                        assignmentCommentText: this.commentText,
                        studentId: this.currentStudent.userId
                    }, (response) => {
                        this.currentComments.push(response.data);
                        this.commentText = '';
                        this.scrollToBottom();
                        this.modal.hide();
                    }, err => {
                        console.error('Error submitting comment:', err);
                    });
                }
            },
            scrollToBottom() {
                if (this.$refs.commentsContainer) {
                    this.$nextTick(() => {
                        const container = this.$refs.commentsContainer;
                        container.scrollTop = container.scrollHeight;
                        this.updateCommentsContainerShadow();
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
                if (this.isStudent) {
                    window.location.href = `assignments/${assignment.assignmentId}`;
                } else {
                    this.openAssignmentModal(assignment, student);
                }
            },
            toggleInstructions() {
                this.instructionsVisible = !this.instructionsVisible;
            },
            getRegularGrades(subject) {
                // For non_numeric, exclude 'A' as it's handled by dropdown
                if (subject.gradingSystem === 'non_numeric') {
                    return ['MA', null];
                }
                // For numeric, include all numeric grades (5+ is handled separately)
                return [...this.gradingSystems[subject.gradingSystem], null];
            },
            getSelectedAGradeDisplay() {
                return this.selectedAVariant || 'A';
            },
            selectGrade(grade) {
                if (grade === '5+') {
                    this.selectedGrade = '5';
                    this.selectedAVariant = 'A+++';
                } else if (grade?.startsWith('A')) {
                    this.selectedGrade = 'A';
                    this.selectedAVariant = grade;
                } else {
                    this.selectedGrade = grade;
                    this.selectedAVariant = null;
                }

                // Update comment if it hasn't been manually edited or is empty
                if (!this.commentTouched || !this.commentText.trim()) {
                    let feedbackLevel = 'positive';
                    
                    if (this.selectedAVariant) {
                        feedbackLevel = {
                            'A': 'positive',
                            'A+': 'excellent',
                            'A++': 'outstanding',
                            'A+++': 'exceptional'
                        }[this.selectedAVariant];
                    } else if (this.selectedGrade) {
                        feedbackLevel = {
                            '3': 'positive',
                            '4': 'excellent',
                            '5': 'outstanding'
                        }[this.selectedGrade] || 'positive';
                    }

                    if (feedbackLevel) {
                        const feedbackArray = this.feedbackTemplates[feedbackLevel];
                        const randomIndex = Math.floor(Math.random() * feedbackArray.length);
                        this.commentText = feedbackArray[randomIndex];
                        this.commentTouched = false;  // Reset touched state as we're using a template
                    }
                }
            },
            removeGrade() {
                if (confirm('Kas olete kindel, et soovite hinde eemaldada?')) {
                    ajax('api/assignments/grade', {
                        assignmentId: this.currentAssignment.assignmentId,
                        studentId: this.currentStudent.userId,
                        grade: null,
                        feedback: 'Hinne eemaldatud'
                    }, () => {
                        const oldGrade = this.currentAssignment.grade;
                        this.currentAssignment.grade = null;
                        this.selectedGrade = null;
                        
                        // Add a comment about grade removal
                        ajax('api/assignments/addComment', {
                            assignmentId: this.currentAssignment.assignmentId,
                            assignmentCommentText: `Hinne <span class="badge ${this.getBadgeClassForGrade(oldGrade)}">${oldGrade}</span> eemaldatud`,
                            studentId: this.currentStudent.userId
                        }, (response) => {
                            this.currentComments.push(response.data);
                            this.scrollToBottom();
                        });
                    }, err => {
                        console.error('Error removing grade:', err);
                    });
                }
            },
            getBadgeClassForGrade(grade) {
                if (['MA', '1', '2'].includes(grade)) return 'bg-danger';
                if (['3', '4', '5', 'A'].includes(grade)) return 'bg-success';
                return 'bg-secondary';
            },
            saveGrade() {
                if (!this.selectedGrade) return;
                
                ajax('api/assignments/grade', {
                    assignmentId: this.currentAssignment.assignmentId,
                    studentId: this.currentStudent.userId,
                    grade: this.selectedGrade,
                    feedback: this.gradingFeedback
                }, () => {
                    this.currentAssignment.grade = this.selectedGrade;
                    if (this.gradingFeedback) {
                        this.currentComments.push({
                            assignmentCommentAuthorName: 'Süsteem',
                            assignmentCommentCreatedAt: new Date().toLocaleString('et-EE'),
                            assignmentCommentText: `Hinne: ${this.selectedGrade}\n\n${this.gradingFeedback}`,
                            assignmentCommentGrade: this.currentAssignment.grade,
                            assignmentCommentIsProposedSolution: 0
                        });
                    }
                    this.scrollToBottom();
                }, err => {
                    console.error('Error saving grade:', err);
                });
            },
            getPossibleGrades(subject) {
                let grades = this.gradingSystems[subject.gradingSystem] || this.gradingSystems.numeric_1_5;
                
                // Kui õpilasel on hinne, mis ei ole hindamissüsteemis, lisame selle
                if (this.currentAssignment?.grade && !grades.includes(this.currentAssignment.grade)) {
                    grades = [...grades, this.currentAssignment.grade];
                }
                
                return grades;
            },
            handleGradeClick(comment, grade) {
                if (!this.isTeacher) return;
                
                comment.selectedGrade = grade;
                comment.showFeedbackForm = true;
                comment.feedback = '';
            },
            submitGrade(comment) {
                ajax('api/assignments/gradeComment', {
                    assignmentId: this.currentAssignment.assignmentId,
                    assignmentCommentId: comment.assignmentCommentId,
                    grade: comment.selectedGrade,
                    feedback: comment.feedback,
                    studentId: this.currentStudent.userId
                }, () => {
                    comment.assignmentCommentGrade = comment.selectedGrade;
                    comment.showFeedbackForm = false;
                    this.currentAssignment.grade = comment.selectedGrade;

                    // Update the grid's studentProgress data
                    for (const group of Object.values(this.groups)) {
                        for (const subject of Object.values(group.subjects)) {
                            if (subject.assignments && subject.assignments[this.currentAssignment.assignmentId]) {
                                if (!subject.assignments[this.currentAssignment.assignmentId].studentProgress) {
                                    subject.assignments[this.currentAssignment.assignmentId].studentProgress = {};
                                }
                                if (!subject.assignments[this.currentAssignment.assignmentId].studentProgress[this.currentStudent.userId]) {
                                    subject.assignments[this.currentAssignment.assignmentId].studentProgress[this.currentStudent.userId] = {};
                                }
                                subject.assignments[this.currentAssignment.assignmentId].studentProgress[this.currentStudent.userId].grade = comment.selectedGrade;
                                subject.assignments[this.currentAssignment.assignmentId].studentProgress[this.currentStudent.userId].assignmentStatusId = comment.selectedGrade ? '3' : '2';
                            }
                        }
                    }

                    this.modal.hide();
                }, err => {
                    console.error('Error saving grade:', err);
                });
            },
            handleCommentInput(event) {
                // Only mark as touched if the current value is different from any template
                const allTemplates = [
                    ...this.feedbackTemplates.positive,
                    ...this.feedbackTemplates.excellent,
                    ...this.feedbackTemplates.outstanding,
                    ...this.feedbackTemplates.exceptional
                ];
                this.commentTouched = !allTemplates.includes(event.target.value);
            },
            updateCommentsContainerShadow() {
                const container = this.$refs.commentsContainer;
                if (container) {
                    const hasOverflow = container.scrollHeight > container.clientHeight;
                    container.classList.toggle('has-overflow', hasOverflow && container.scrollTop > 10);

                    // Add scroll event listener
                    container.onscroll = () => {
                        container.classList.toggle('has-overflow', container.scrollTop > 10);
                    };
                }
            },
            scrollToTop() {
                if (this.$refs.commentsContainer) {
                    this.$refs.commentsContainer.scrollTop = 0;
                }
            },
            startEditingTitle() {
                this.editingTitle = true;
                this.editedTitle = this.currentAssignment.assignmentName;
                this.$nextTick(() => {
                    this.$refs.titleInput.focus();
                });
            },
            cancelEditingTitle() {
                this.editingTitle = false;
                this.editedTitle = '';
            },
            saveTitle() {
                if (!this.editedTitle.trim()) return;
                
                ajax('api/assignments/updateTitle', {
                    assignmentId: this.currentAssignment.assignmentId,
                    title: this.editedTitle.trim()
                }, () => {
                    this.currentAssignment.assignmentName = this.editedTitle.trim();
                    
                    // Update the title in the grid
                    for (const group of Object.values(this.groups)) {
                        for (const subject of Object.values(group.subjects)) {
                            if (subject.assignments && subject.assignments[this.currentAssignment.assignmentId]) {
                                subject.assignments[this.currentAssignment.assignmentId].assignmentName = this.editedTitle.trim();
                            }
                        }
                    }
                    
                    this.editingTitle = false;
                }, err => {
                    console.error('Error updating title:', err);
                });
            },
            startEditingInstructions() {
                this.editingInstructions = true;
                this.editedInstructions = this.currentAssignment.assignmentInstructions;
                this.$nextTick(() => {
                    this.$refs.instructionsInput.focus();
                });
            },
            cancelEditingInstructions() {
                this.editingInstructions = false;
                this.editedInstructions = '';
            },
            saveInstructions() {
                ajax('api/assignments/updateInstructions', {
                    assignmentId: this.currentAssignment.assignmentId,
                    instructions: this.editedInstructions
                }, () => {
                    this.currentAssignment.assignmentInstructions = this.editedInstructions;
                    this.editingInstructions = false;
                }, err => {
                    console.error('Error updating instructions:', err);
                });
            },
        },
        mounted() {
            this.modal = new bootstrap.Modal(document.getElementById('assignmentModal'));
            this.modal._element.addEventListener('shown.bs.modal', () => {
                this.scrollToBottom();
                this.updateCommentsContainerShadow();
            });

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }
    });

    app.mount('#app');
</script>
