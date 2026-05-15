<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
    <Head title="D. - Digital Archive & Mapping System" />

    <div
        class="relative min-h-screen bg-cover bg-center"
        style="background-image: url('/images/landingbg.png')"
    >
        <!-- Dark Overlay -->
        <div class="absolute inset-0 bg-black/60"></div>

        <!-- Main Wrapper -->
        <div class="relative z-10 flex min-h-screen flex-col">

            <!-- Navbar -->
            <header class="flex items-center justify-between px-8 py-6">

                <!-- Logo + Title -->
                <div class="flex items-center gap-4 text-white">

                    <!-- NIA Logo -->
                    <img
                        src="/favicon.ico"
                        alt="NIA Logo"
                        class="h-16 w-16 rounded-full border border-white/30 bg-white/10 object-cover shadow-lg"
                    />

                    <!-- System Name -->
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-wide">
                            DAMS
                        </h1>

                        <p class="text-sm text-white/80">
                            Digital Archive & Mapping System
                        </p>

                        <p class="text-xs text-emerald-300">
                            National Irrigation Administration
                        </p>
                    </div>
                </div>

            </header>

            <!-- Hero Section -->
            <main class="flex flex-1 items-center px-8 py-16">
                <div class="max-w-4xl text-white">
                    <h2 class="mb-6 text-5xl font-extrabold leading-tight">
                        Separated Employee Digital Archive & Mapping System
                    </h2>

                    <p class="mb-8 text-lg leading-relaxed text-white/90">
                        DAMS is a centralized platform designed to manage,
                        archive, track, and organize records of separated
                        employees, including employment documents,
                        requirements, storage locations, and reports
                        securely and efficiently.
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <Link
                            href="/login"
                            class="rounded-2xl bg-emerald-600 px-8 py-4 text-lg font-semibold text-white shadow-lg transition hover:bg-emerald-700"
                        >
                            Get Started
                        </Link>

                        <a
                            href="#features"
                            class="rounded-2xl border border-white/40 bg-white/10 px-8 py-4 text-lg font-semibold text-white backdrop-blur-md transition hover:bg-white/20"
                        >
                            Learn More
                        </a>
                    </div>
                </div>
            </main>

            <!-- Features -->
            <section
                id="features"
                class="bg-black/60 px-8 py-14 backdrop-blur-md"
            >
                <div class="mx-auto max-w-7xl text-white">

                    <h3 class="mb-4 text-center text-4xl font-bold">
                        System Features & Functions
                    </h3>

                    <p class="mx-auto mb-12 max-w-3xl text-center text-white/80">
                        DAMS helps the organization securely manage
                        and archive separated employee records, documents,
                        locations, and reports in one centralized system.
                    </p>

                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">

                        <!-- Feature -->
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-6">
                            <!-- Blue Monochrome Filter Applied Below -->
                            <div class="mb-4 text-4xl" style="filter: sepia(100%) hue-rotate(190deg) saturate(500%);">📁</div>

                            <h4 class="mb-3 text-2xl font-semibold">
                                Inactive 201 Folder Management
                            </h4>

                            <p class="leading-relaxed text-white/80">
                                Create and organize digital folders for
                                separated employees records.
                            </p>
                        </div>

                        <!-- Feature -->
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-6">
                            <div class="mb-4 text-4xl" style="filter: sepia(100%) hue-rotate(190deg) saturate(500%);">📄</div>

                            <h4 class="mb-3 text-2xl font-semibold">
                                Requirement Tracking
                            </h4>

                            <p class="leading-relaxed text-white/80">
                                Track separation requirements and supporting documents
                                to ensure full administrative compliance.
                            </p>
                        </div>

                        <!-- Feature -->
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-6">
                            <div class="mb-4 text-4xl" style="filter: sepia(100%) hue-rotate(190deg) saturate(500%);">🗺️</div>

                            <h4 class="mb-3 text-2xl font-semibold">
                                Physical File Mapping
                            </h4>

                            <p class="leading-relaxed text-white/80">
                                Locate physical employee records through
                                mapped storage locations and storage paths.
                            </p>
                        </div>

                        <!-- Feature -->
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-6">
                            <div class="mb-4 text-4xl" style="filter: sepia(100%) hue-rotate(190deg) saturate(500%);">🔍</div>

                            <h4 class="mb-3 text-2xl font-semibold">
                                Global Search
                            </h4>

                            <p class="leading-relaxed text-white/80">
                                Quickly search separated employee records,
                                users, and physical storage locations.
                            </p>
                        </div>

                        <!-- Feature -->
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-6">
                            <div class="mb-4 text-4xl" style="filter: sepia(100%) hue-rotate(190deg) saturate(500%);">📊</div>

                            <h4 class="mb-3 text-2xl font-semibold">
                                Reports & Export
                            </h4>

                            <p class="leading-relaxed text-white/80">
                                Generate PDF and Excel reports of
                                Inactive 201 Files.
                            </p>
                        </div>

                        <!-- Feature -->
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-6">
                            <div class="mb-4 text-4xl" style="filter: sepia(100%) hue-rotate(190deg) saturate(500%);">📝</div>

                            <h4 class="mb-3 text-2xl font-semibold">
                                Activity Monitoring
                            </h4>

                            <p class="leading-relaxed text-white/80">
                                Monitor all user actions including
                                creation, updates, deletion.
                            </p>
                        </div>

                        <!-- Feature -->
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-6">
                            <div class="mb-4 text-4xl" style="filter: sepia(100%) hue-rotate(190deg) saturate(500%);">👥</div>

                            <h4 class="mb-3 text-2xl font-semibold">
                                User Management
                            </h4>

                            <p class="leading-relaxed text-white/80">
                                Manage administrator and user accounts
                                with secure role-based access control.
                            </p>
                        </div>

                        <!-- Feature -->
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-6">
                            <div class="mb-4 text-4xl" style="filter: sepia(100%) hue-rotate(190deg) saturate(500%);">📈</div>

                            <h4 class="mb-3 text-2xl font-semibold">
                                Dashboard Analytics
                            </h4>

                            <p class="leading-relaxed text-white/80">
                                View statistics, completion rates,
                                recent records, and storage summaries.
                            </p>
                        </div>

                        <!-- Feature -->
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-6">
                            <div class="mb-4 text-4xl" style="filter: sepia(100%) hue-rotate(190deg) saturate(500%);">🔒</div>

                            <h4 class="mb-3 text-2xl font-semibold">
                                Data Security
                            </h4>

                            <p class="leading-relaxed text-white/80">
                                Secure confidentiality of separated employees
                                records through secure data handling
                                and privacy compliance.
                            </p>
                        </div>

                    </div>
                </div>
            </section>

            <!-- Instructions -->
            <section class="bg-black/70 px-8 py-14">
                <div class="mx-auto max-w-6xl text-white">

                    <h3 class="mb-10 text-center text-4xl font-bold">
                        How to Use DAMS
                    </h3>

                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">

                        <div class="rounded-2xl bg-white/10 p-6">
                            <div class="mb-4 text-4xl">1️⃣</div>

                            <h4 class="mb-3 text-xl font-semibold">
                                Login
                            </h4>

                            <p class="text-white/80">
                                Access the system using your authorized
                                account credentials.
                            </p>
                        </div>

                        <div class="rounded-2xl bg-white/10 p-6">
                            <div class="mb-4 text-4xl">2️⃣</div>

                            <h4 class="mb-3 text-xl font-semibold">
                                Manage Employee Records
                            </h4>

                            <p class="text-white/80">
                                Create records and upload documents
                                for separated employees.
                            </p>
                        </div>

                        <div class="rounded-2xl bg-white/10 p-6">
                            <div class="mb-4 text-4xl">3️⃣</div>

                            <h4 class="mb-3 text-xl font-semibold">
                                Track Records
                            </h4>

                            <p class="text-white/80">
                                Monitor the status of separated employee records,
                                requirements, and storage locations.
                            </p>
                        </div>

                        <div class="rounded-2xl bg-white/10 p-6">
                            <div class="mb-4 text-4xl">4️⃣</div>

                            <h4 class="mb-3 text-xl font-semibold">
                                Generate Reports
                            </h4>

                            <p class="text-white/80">
                                Export reports and logs in
                                PDF or Excel format.
                            </p>
                        </div>

                    </div>
                </div>
            </section>

            <!-- Data Privacy -->
            <section
                id="privacy"
                class="bg-black/80 px-8 py-10"
            >
                <div class="mx-auto max-w-5xl text-white">

                    <h3 class="mb-4 text-3xl font-bold">
                        Data Privacy Act of 2012
                    </h3>

                    <p class="mb-4 leading-relaxed text-white/90">
                        This system complies with Republic Act No. 10173,
                        also known as the Data Privacy Act of 2012,
                        ensuring that all personal and organizational data
                        is processed securely and responsibly.
                    </p>

                    <p class="leading-relaxed text-white/80">
                        DAMS implements appropriate security measures to
                        protect separated employee records, archived documents,
                        and confidential information in accordance with the
                        standards established by the National Privacy
                        Commission (NPC).
                    </p>
                </div>
            </section>

            <!-- Footer -->
            <footer class="bg-black px-8 py-6 text-center text-sm text-white/60">
                © 2026 DAMS — Digital Archive & Mapping System <br />
                National Irrigation Administration <br />
                All Rights Reserved.
            </footer>

        </div>
    </div>
</template>
