<template>
    <div class="position-ref full-height">
            <nav class="navbar navbar-expand-md navbar-light navbar-laravel">
                <div class="container">
                    <ul class="navbar-nav">
                        <router-link :to="{ name: 'welcome' }" class="nav-link">Home</router-link>
                        <router-link :to="{ name: 'page' }" class="nav-link" >Spa-Page</router-link>
                        <router-link :to="{ name: 'users.index' }">Users</router-link>
                    </ul>
                </div>
            </nav>
        <main class="container">
            <router-view></router-view>
        </main>
    </div>
</template>
<script>
export default {}
</script>