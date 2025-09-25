<div class="row">
    <div class="col">
        <h1>Kursused</h1>
    </div>
    <div class="col-auto">
        <?php if ($this->auth->userIsTeacher || $this->auth->userIsAdmin): ?>
        <button id="btn-new-course" class="btn btn-primary">Uus kursus</button>
        <?php endif; ?>
    </div>
</div>

<div class="list-group mt-3">
    <?php foreach ($this->courses as $course): ?>
        <a href="courses/<?= $course['id'] ?>" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1"><?= htmlspecialchars($course['name']) ?></h5>
                <small><?= htmlspecialchars($course['visibility'] ?? '') ?></small>
            </div>
            <p class="mb-1"><?= htmlspecialchars($course['description'] ?? '') ?></p>
        </a>
    <?php endforeach; ?>
</div>

<!-- New Course Modal -->
<?php if ($this->auth->userIsTeacher || $this->auth->userIsAdmin): ?>
<div class="modal fade" id="newCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Uus kursus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="newCourseForm" method="post" action="<?= BASE_URL ?>courses/create">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nimi <span class="text-danger">*</span></label>
                    <input name="name" class="form-control" required />
                </div>
                <div class="mb-3">
                    <label class="form-label">Kirjeldus</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nähtavus</label>
                        <select name="visibility" class="form-select">
                            <option value="private" selected>Privaatne</option>
                            <option value="public">Avalik</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Olek</label>
                        <select name="status" class="form-select">
                            <option value="inactive" selected>Mitteaktiivne</option>
                            <option value="active">Aktiivne</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Filtreeri</label>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <select id="groupFilter" class="form-select">
                                <option value="">— Kõik grupid —</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select id="subjectFilter" class="form-select">
                                <option value="">— Kõik ained —</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select id="teacherFilter" class="form-select">
                                <option value="">— Kõik õpetajad —</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ülesanne</label>
                    <!-- Filtered input with suggestions instead of a large select -->
                    <input type="text" class="form-control" id="assignmentFilter" placeholder="Otsi ülesannet nime järgi..." autocomplete="off" />
                    <input type="hidden" name="assignmentId" id="assignmentIdHidden" value="" />
                    <div class="list-group mt-1 d-none" id="assignmentSuggestions" style="max-height:220px; overflow:auto;"></div>
                    <div class="form-text text-muted" id="assignmentDropdownHelp"></div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tühista</button>
                <button type="submit" class="btn btn-primary">Loo kursus</button>
            </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    (function () {
        // lightweight styles for the suggestion list to look more professional
        const style = document.createElement('style');
        style.textContent = `
            .assignment-suggestion { display:flex; flex-direction:column; }
            .assignment-suggestion .title { font-weight:600; }
            .assignment-suggestion .meta { font-size:0.85rem; color:var(--bs-muted); }
            .assignment-suggestion .badge-group { margin-left:6px; font-size:0.7rem; }
            #assignmentSuggestions .list-group-item:focus, #assignmentSuggestions .list-group-item:hover { background-color: #f8f9fa; outline: none; }
            #assignmentSuggestions .list-group-item.selected { background-color: #e9ecef; }
        `;
        document.head.appendChild(style);
        const btn = document.getElementById('btn-new-course');
        if (!btn) return;
        const modalEl = document.getElementById('newCourseModal');
        const assignmentFilter = document.getElementById('assignmentFilter');
        const assignmentIdHidden = document.getElementById('assignmentIdHidden');
        const assignmentSuggestions = document.getElementById('assignmentSuggestions');
        const assignmentHelp = document.getElementById('assignmentDropdownHelp');
        let bsModal;
        btn.addEventListener('click', function () {
            // lazy-load assignments for dropdown grouped by subject
            fetch('<?= BASE_URL ?>courses/assignments_for_dropdown')
                .then(r => r.json())
                .then(data => {
                    // Flatten assignments into an array with subject info for filtering
                    // data: { subjects: [{ id, name, assignments: [{id, name}] }], teacherHasSubjects: bool }
                    assignmentFilter.value = '';
                    assignmentIdHidden.value = '';
                    assignmentSuggestions.innerHTML = '';
                    assignmentSuggestions.classList.add('d-none');

                    if (!data.teacherHasSubjects) {
                        assignmentFilter.disabled = true;
                        assignmentHelp.textContent = 'Sulle määratud aineid ei ole';
                        return;
                    }
                    assignmentFilter.disabled = false;
                    assignmentHelp.textContent = '';

                    // populate group, subject and teacher selects
                    const groupFilter = document.getElementById('groupFilter');
                    const subjectFilter = document.getElementById('subjectFilter');
                    const teacherFilter = document.getElementById('teacherFilter');
                    // clear existing options except the default
                    groupFilter.querySelectorAll('option:not([value=""])').forEach(n => n.remove());
                    subjectFilter.querySelectorAll('option:not([value=""])').forEach(n => n.remove());
                    teacherFilter.querySelectorAll('option:not([value=""])').forEach(n => n.remove());
                    data.groups.forEach(g => {
                        const o = document.createElement('option'); o.value = g.groupId; o.textContent = g.groupName; groupFilter.appendChild(o);
                    });
                    // build groupId -> groupName map to annotate subjects
                    const groupsMap = {};
                    data.groups.forEach(g => { groupsMap[g.groupId] = g.groupName; });
                    // build teacherId -> teacherName map
                    const teachersMap = {};
                    data.teachers.forEach(t => { teachersMap[t.userId] = t.userName; });
                    // subjects (dedupe by id) and show group name after subject to avoid duplicates
                    const seenSubjects = new Set();
                    data.subjects.forEach(s => {
                        if (seenSubjects.has(s.id)) return;
                        seenSubjects.add(s.id);
                        const grp = groupsMap[s.groupId] || '';
                        const o = document.createElement('option'); o.value = s.id; o.textContent = grp ? `${s.name} (${grp})` : s.name; subjectFilter.appendChild(o);
                    });
                    data.teachers.forEach(t => {
                        const o = document.createElement('option'); o.value = t.userId; o.textContent = t.userName; teacherFilter.appendChild(o);
                    });

                    // Build a flat list for quick search: { id, name, subjectName, subjectId, groupId, teacherId, groupName }
                    const flat = [];
                    data.subjects.forEach(subject => {
                        subject.assignments.forEach(a => {
                            flat.push({ id: a.id, name: a.name, subject: subject.name, subjectId: subject.id, groupId: subject.groupId, teacherId: subject.teacherId, groupName: groupsMap[subject.groupId] || '', teacherName: teachersMap[subject.teacherId] || '' });
                        });
                    });

                    

                    // simple client-side filter & suggestion rendering
                    let lastQuery = '';
                    const renderSuggestions = (items) => {
                        assignmentSuggestions.innerHTML = '';
                        if (!items.length) {
                            assignmentSuggestions.classList.add('d-none');
                            return;
                        }
                        assignmentSuggestions.classList.remove('d-none');
                        items.forEach(it => {
                            const el = document.createElement('a');
                            el.href = '#';
                            el.className = 'list-group-item list-group-item-action py-2';
                            el.dataset.assignmentId = it.id;
                            const wrapper = document.createElement('div'); wrapper.className = 'assignment-suggestion';
                            const title = document.createElement('div'); title.className = 'title'; title.textContent = it.name;
                            const meta = document.createElement('div'); meta.className = 'meta'; meta.textContent = it.subject;
                            // attach group badge if present
                            if (it.groupName) {
                                const badge = document.createElement('span'); badge.className = 'badge bg-secondary badge-group'; badge.textContent = it.groupName; meta.appendChild(badge);
                            }
                            // attach teacher badge if present (same look as group badge)
                            if (it.teacherName) {
                                const tbadge = document.createElement('span'); tbadge.className = 'badge bg-secondary badge-group'; tbadge.textContent = it.teacherName; meta.appendChild(tbadge);
                            }
                            wrapper.appendChild(title); wrapper.appendChild(meta);
                            el.appendChild(wrapper);
                            el.addEventListener('click', (e) => {
                                e.preventDefault();
                                assignmentFilter.value = it.name;
                                assignmentIdHidden.value = it.id;
                                assignmentSuggestions.classList.add('d-none');
                            });
                            assignmentSuggestions.appendChild(el);
                        });
                    };

                    // grouped rendering: show all subjects and their assignments (respecting current filters)
                    const renderGroupedSubjects = () => {
                        assignmentSuggestions.innerHTML = '';
                        const selGroup = groupFilter.value || null;
                        const selTeacher = teacherFilter.value || null;
                        const selSubject = subjectFilter.value || null;
                        let any = false;
                        data.subjects.forEach(sub => {
                            if (selGroup && String(sub.groupId) !== String(selGroup)) return;
                            if (selTeacher && String(sub.teacherId) !== String(selTeacher)) return;
                            if (selSubject && String(sub.id) !== String(selSubject)) return;
                            // append assignments only (no subject header)
                            sub.assignments.forEach(a => {
                                const el = document.createElement('a');
                                el.href = '#';
                                el.className = 'list-group-item list-group-item-action py-2';
                                el.dataset.assignmentId = a.id;
                                const wrapper = document.createElement('div'); wrapper.className = 'assignment-suggestion';
                                const title = document.createElement('div'); title.className = 'title'; title.textContent = a.name;
                                const meta = document.createElement('div'); meta.className = 'meta'; meta.textContent = sub.name;
                                if (groupsMap && groupsMap[sub.groupId]) {
                                    const badge = document.createElement('span'); badge.className = 'badge bg-secondary badge-group'; badge.textContent = groupsMap[sub.groupId]; meta.appendChild(badge);
                                }
                                // attach teacher name for this subject if available (same style as group)
                                if (teachersMap && teachersMap[sub.teacherId]) {
                                    const tbadge = document.createElement('span'); tbadge.className = 'badge bg-secondary badge-group'; tbadge.textContent = teachersMap[sub.teacherId]; meta.appendChild(tbadge);
                                }
                                wrapper.appendChild(title); wrapper.appendChild(meta);
                                el.appendChild(wrapper);
                                el.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    assignmentFilter.value = a.name;
                                    assignmentIdHidden.value = a.id;
                                    assignmentSuggestions.classList.add('d-none');
                                });
                                assignmentSuggestions.appendChild(el);
                            });
                            any = true;
                        });
                        if (!any) {
                            assignmentSuggestions.classList.add('d-none');
                        } else {
                            assignmentSuggestions.classList.remove('d-none');
                        }
                    };

                    const getFilters = () => ({
                        group: groupFilter.value || null,
                        teacher: teacherFilter.value || null,
                        subject: subjectFilter.value || null,
                    });

                    const applyFilter = () => {
                        const raw = assignmentFilter.value;
                        const q = raw.trim().toLowerCase();
                        const filters = getFilters();
                        // split by whitespace into tokens; tokens act as AND
                        const tokens = raw.split(/\s+/).filter(Boolean).map(t => t.toLowerCase());
                        if (tokens.length === 0) {
                            assignmentIdHidden.value = '';
                            renderSuggestions([]);
                            return;
                        }
                        // if query string unchanged and filters unchanged, skip
                        if (raw === lastQuery && !filtersChanged) return;
                        lastQuery = raw;
                        // filter by tokens (OR across tokens) and by group/teacher/subject if selected
                        const results = flat.filter(it => {
                            const name = (it.name || '').toLowerCase();
                            const subject = (it.subject || '').toLowerCase();
                            const group = (it.groupName || '').toLowerCase();
                            const teacher = (it.teacherName || '').toLowerCase();
                            // all tokens must match (AND) in name OR subject OR group OR teacher
                            const matchesAll = tokens.every(tok => name.includes(tok) || subject.includes(tok) || group.includes(tok) || teacher.includes(tok));
                            if (!matchesAll) return false;
                            const matchesGroup = !filters.group || String(it.groupId) === String(filters.group);
                            const matchesTeacher = !filters.teacher || String(it.teacherId) === String(filters.teacher);
                            const matchesSubject = !filters.subject || String(it.subjectId) === String(filters.subject);
                            return matchesGroup && matchesTeacher && matchesSubject;
                        }).slice(0, 50);
                        renderSuggestions(results);
                        filtersChanged = false;
                    };

                    let filtersChanged = false;
                    assignmentFilter.addEventListener('input', applyFilter);
                    assignmentFilter.addEventListener('focus', () => {
                        // if empty, show grouped list like earlier dropdown
                        if (assignmentFilter.value.trim() === '') {
                            renderGroupedSubjects();
                        }
                    });
                    assignmentFilter.addEventListener('click', () => {
                        if (assignmentFilter.value.trim() === '') renderGroupedSubjects();
                    });
                    groupFilter.addEventListener('change', () => { filtersChanged = true; assignmentIdHidden.value = ''; applyFilter(); });
                    subjectFilter.addEventListener('change', () => { filtersChanged = true; assignmentIdHidden.value = ''; applyFilter(); });
                    teacherFilter.addEventListener('change', () => { filtersChanged = true; assignmentIdHidden.value = ''; applyFilter(); });

                    // hide suggestions when clicking outside
                    document.addEventListener('click', (ev) => {
                        if (!assignmentSuggestions.contains(ev.target) && ev.target !== assignmentFilter) {
                            assignmentSuggestions.classList.add('d-none');
                        }
                    });
                }).catch(err => {
                    assignmentHelp.textContent = 'Ülesannete laadimine ebaõnnestus';
                    assignmentFilter.disabled = true;
                });

            // show modal
            if (!bsModal) bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        });
    })();
</script>
