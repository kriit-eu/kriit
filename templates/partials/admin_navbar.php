<nav class="nav nav-pills flex-column flex-sm-row" style="margin-bottom: 4em">
    <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('/groups') ?>" href="/groups">Grupid</a>
    <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('/subjects') ?>" href="/subjects">Ained</a>
    <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('admin/logs') ?>" href="admin/logs">Logi</a>
    <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('admin/users') ?>" href="admin/users">Administraatorid</a>
</nav>
