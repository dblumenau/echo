export function useRequestFormatters() {
    const getMethodColor = (method: string) => {
        const colors: Record<string, string> = {
            GET: 'bg-emerald-500 hover:bg-emerald-600',
            POST: 'bg-blue-500 hover:bg-blue-600',
            PUT: 'bg-amber-500 hover:bg-amber-600',
            PATCH: 'bg-orange-500 hover:bg-orange-600',
            DELETE: 'bg-red-500 hover:bg-red-600',
        };
        return colors[method] || 'bg-gray-500 hover:bg-gray-600';
    };

    const getMethodIcon = (method: string) => {
        const icons: Record<string, string> = {
            GET: '↓',
            POST: '↑',
            PUT: '↻',
            PATCH: '⟳',
            DELETE: '×',
        };
        return icons[method] || '•';
    };

    const truncate = (str: string | null, length: number = 50) => {
        if (!str) return '';
        return str.length > length ? str.slice(0, length) + '...' : str;
    };

    const formatJson = (data: any) => {
        try {
            if (typeof data === 'string') {
                const parsed = JSON.parse(data);
                return JSON.stringify(parsed, null, 2);
            }
            return JSON.stringify(data, null, 2);
        } catch {
            return data;
        }
    };

    const isJsonContent = (headers: Record<string, any>) => {
        const contentType = headers['content-type']?.[0] || '';
        return contentType.includes('application/json');
    };

    const getContentTypeIcon = (headers: Record<string, any>) => {
        const contentType = headers['content-type']?.[0] || '';
        if (contentType.includes('application/json')) return '{ }';
        if (contentType.includes('application/x-www-form-urlencoded')) return '≡';
        if (contentType.includes('multipart/form-data')) return '📎';
        if (contentType.includes('text/')) return '📄';
        return '📦';
    };

    const formatRequestBody = (body: string | null, headers: Record<string, any>) => {
        if (!body) return null;
        
        const contentType = headers['content-type']?.[0] || '';
        
        // JSON
        if (contentType.includes('application/json')) {
            try {
                return { type: 'json', data: JSON.parse(body) };
            } catch {
                return { type: 'text', data: body };
            }
        }
        
        // Form data
        if (contentType.includes('application/x-www-form-urlencoded')) {
            try {
                const params = new URLSearchParams(body);
                const data: Record<string, string> = {};
                params.forEach((value, key) => {
                    data[key] = value;
                });
                return { type: 'form', data };
            } catch {
                return { type: 'text', data: body };
            }
        }
        
        return { type: 'text', data: body };
    };

    const getHeaderClass = (headerName: string): string => {
        const name = headerName.toLowerCase();
        
        // Authentication headers
        if (name.includes('authorization') || name.includes('x-api-key') || name.includes('x-auth-token') || name.includes('token')) {
            return 'dashboard-header-auth';
        }
        
        // Content headers
        if (name.includes('content-') || name === 'content') {
            return 'dashboard-header-content';
        }
        
        // Request headers
        if (name.includes('accept') || name === 'user-agent' || name.includes('x-requested-with')) {
            return 'dashboard-header-request';
        }
        
        // CORS headers
        if (name.includes('origin') || name.includes('access-control') || name.includes('cors')) {
            return 'dashboard-header-cors';
        }
        
        // Host headers
        if (name === 'host' || name === 'referer' || name === 'referrer') {
            return 'dashboard-header-host';
        }
        
        return 'dashboard-header-default';
    };

    return {
        getMethodColor,
        getMethodIcon,
        truncate,
        formatJson,
        isJsonContent,
        getContentTypeIcon,
        formatRequestBody,
        getHeaderClass
    };
}