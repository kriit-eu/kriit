<?php require 'vendor/autoload.php';


$actions = ['new-assignment', 'edit-assignment', 'delete-assignment'];

foreach ($actions as $action) {

    if (!empty($_GET[$action])) {

        $pusher = new Pusher\Pusher(
            '18418126f1c8b9e2828a',
            'ec2754f5b421121360e3',
            '1923082',
            [
                'cluster' => 'eu',
                'useTLS' => true
            ]
        );

        $data = json_decode(file_get_contents('php://input'), true);

        if ($action === 'new-assignment') {
            \App\Db::insert('assignments', [
                'id' => $data['id'],
                'name' => $data['name'],
                'done' => $data['done']
            ]);
        }

        if ($action === 'delete-assignment') {
            \App\Db::delete(
                'assignments',
                'id = ?',
                [$data['id']]);
        }

        $pusher->trigger('assignments-channel', $action, $data);
        echo json_encode(['status' => 'success']);
        exit();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

<div id="app">
    <div v-for="assignment in assignments" :key="assignment.id">
        <input type="text" v-model="assignment.name">
        <button @click="editAssignment(assignment)">Edit</button>
        <button @click="deleteAssignment(assignment.id)">Delete</button>
    </div>
    <input type="text" v-model="newAssignmentName" placeholder="Enter new assignment">
    <button @click="addNewAssignment">Add Assignment</button>
</div>
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>

    var pusher = new Pusher('18418126f1c8b9e2828a', {
        cluster: 'eu'
    });

    const channel = pusher.subscribe('assignments-channel');

    new Vue({
        el: '#app',
        data: {
            assignments: [
                {id: 1, name: "Foo", done: true},
                {id: 2, name: "Bar", done: false},
                {id: 3, name: "Baz", done: true},

            ],
            newAssignmentName: ''
        },
        methods: {
            addNewAssignment() {
                if (this.newAssignmentName.trim() !== '') {
                    const newAssignment = {
                        id: Date.now(),
                        name: this.newAssignmentName,
                        done: false
                    };
                    axios.post('test.php?new-assignment=true', newAssignment);
                    this.newAssignmentName = '';
                }
            },
            editAssignment(assignment) {
                axios.post('test.php?edit-assignment=true', assignment);
            },
            deleteAssignment(id) {
                axios.post('test.php?delete-assignment=true', {id});
            }
        },
        mounted() {
            channel.bind('new-assignment', (data) => {
                this.assignments.push(data);
            });
            channel.bind('edit-assignment', (data) => {
                const index = this.assignments.findIndex(a => a.id === data.id);
                if (index !== -1) {
                    this.$set(this.assignments, index, data);
                }
            });
            channel.bind('delete-assignment', (data) => {
                this.assignments = this.assignments.filter(a => a.id !== data.id);
            });
        }
    })
</script>
</body>
</html>
