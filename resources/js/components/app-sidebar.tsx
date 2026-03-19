import { Link } from '@inertiajs/react';
import {
    BookOpen,
    FileArchive,
    FileText,
    FolderGit2,
    Inbox,
    LayoutGrid,
    Menu,
    Newspaper,
    ReceiptText,
    Settings,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { index as documentsIndex } from '@/routes/cms/documents';
import { index as grmSubmissionsIndex } from '@/routes/cms/grm-submissions';
import { index as menusIndex } from '@/routes/cms/menus';
import { index as newsIndex } from '@/routes/cms/news';
import { index as pagesIndex } from '@/routes/cms/pages';
import { index as procurementsIndex } from '@/routes/cms/procurements';
import { edit as settingsEdit } from '@/routes/cms/settings';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Pages',
        href: pagesIndex(),
        icon: FileText,
    },
    {
        title: 'Settings',
        href: settingsEdit(),
        icon: Settings,
    },
    {
        title: 'Menus',
        href: menusIndex(),
        icon: Menu,
    },
    {
        title: 'News',
        href: newsIndex(),
        icon: Newspaper,
    },
    {
        title: 'Documents',
        href: documentsIndex(),
        icon: FileArchive,
    },
    {
        title: 'GRM',
        href: grmSubmissionsIndex(),
        icon: Inbox,
    },
    {
        title: 'Procurements',
        href: procurementsIndex(),
        icon: ReceiptText,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
