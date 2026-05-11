<template>
    <div>
        <h1>Vista Admin</h1>
        <nav>
            <router-link to="/">Inicio</router-link>
            <router-link to="/about">Sobre Nosotros</router-link>
        </nav>
        <router-view></router-view>
    </div>
</template>

<style>
    nav {
        display: flex;
        gap: 10px;
        padding: 10px;
        background: #f4f4f4;
    }
</style>
