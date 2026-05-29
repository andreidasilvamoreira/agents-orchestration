const builderRoot = document.getElementById('workflow-builder-root');

if (builderRoot) {
    initWorkflowBuilder(builderRoot);
}

function initWorkflowBuilder(root) {
    const NODE_WIDTH = 220;
    const NODE_HEIGHT = 92;
    const payload = JSON.parse(document.getElementById('workflow-builder-payload').textContent);
    const viewport = root.querySelector('[data-canvas-viewport]');
    const canvas = root.querySelector('[data-canvas]');
    const nodesLayer = root.querySelector('[data-nodes-layer]');
    const edgesLayer = root.querySelector('[data-edges-layer]');
    const palette = root.querySelector('[data-agent-palette]');
    const emptyState = root.querySelector('[data-empty-state]');
    const saveForm = root.querySelector('[data-builder-form]');
    const selectedPanel = root.querySelector('[data-selected-step]');
    const selectedHint = root.querySelector('[data-selected-hint]');
    const hiddenDefinition = root.querySelector('textarea[name="definition"]');
    const workflowName = root.querySelector('input[name="name"]');
    const workflowSlug = root.querySelector('input[name="slug"]');
    const workflowDescription = root.querySelector('textarea[name="description"]');
    const workflowTeam = root.querySelector('select[name="agent_team_id"]');
    const workflowActive = root.querySelector('input[name="is_active"]');
    const formTitle = root.querySelector('[data-form-title]');
    const removeStepButton = root.querySelector('[data-remove-step]');
    const clearCanvasButton = root.querySelector('[data-clear-canvas]');
    const zoomInButton = root.querySelector('[data-zoom-in]');
    const zoomOutButton = root.querySelector('[data-zoom-out]');
    const zoomResetButton = root.querySelector('[data-zoom-reset]');
    const zoomLabel = root.querySelector('[data-zoom-label]');
    const connectHint = root.querySelector('[data-connect-hint]');
    const deleteModal = root.querySelector('[data-delete-modal]');
    const deleteModalText = root.querySelector('[data-delete-text]');
    const deleteConfirmButton = root.querySelector('[data-delete-confirm]');
    const deleteCancelButton = root.querySelector('[data-delete-cancel]');

    const state = {
        agents: payload.agents,
        nodes: [],
        edges: [],
        selectedNodeId: null,
        connectSource: null,
        pendingDeleteNodeId: null,
        dragNodeId: null,
        dragOffsetX: 0,
        dragOffsetY: 0,
        panActive: false,
        panStartX: 0,
        panStartY: 0,
        nextNodeIndex: 1,
        transform: {
            scale: 1,
            x: 0,
            y: 0,
        },
    };

    bindPalette();
    bindCanvas();
    bindEditor();
    bindDeleteModal();
    bindZoom();
    loadWorkflow(payload.selectedWorkflow);
    render();

    function bindPalette() {
        palette.querySelectorAll('[data-agent-item]').forEach((item) => {
            item.addEventListener('dragstart', (event) => {
                event.dataTransfer.setData('text/plain', item.dataset.agentId);
            });
        });
    }

    function bindCanvas() {
        viewport.addEventListener('contextmenu', (event) => {
            if (event.target.closest('.builder-node')) {
                return;
            }

            event.preventDefault();
        });

        viewport.addEventListener('mousedown', (event) => {
            if (event.button !== 2 || event.target.closest('.builder-node')) {
                return;
            }

            event.preventDefault();
            state.panActive = true;
            state.panStartX = event.clientX - state.transform.x;
            state.panStartY = event.clientY - state.transform.y;
            viewport.classList.add('is-panning');
        });

        window.addEventListener('mousemove', (event) => {
            if (state.dragNodeId) {
                const node = state.nodes.find((item) => item.id === state.dragNodeId);
                const point = screenToCanvas(event.clientX, event.clientY);

                node.x = clamp(point.x - state.dragOffsetX, 0, canvas.clientWidth - NODE_WIDTH);
                node.y = clamp(point.y - state.dragOffsetY, 0, canvas.clientHeight - NODE_HEIGHT);
                render();
                return;
            }

            if (!state.panActive) {
                return;
            }

            state.transform.x = event.clientX - state.panStartX;
            state.transform.y = event.clientY - state.panStartY;
            applyTransform();
        });

        window.addEventListener('mouseup', () => {
            state.dragNodeId = null;

            if (state.panActive) {
                state.panActive = false;
                viewport.classList.remove('is-panning');
            }
        });

        viewport.addEventListener('wheel', (event) => {
            if (!event.ctrlKey && !event.metaKey) {
                return;
            }

            event.preventDefault();

            const nextScale = clamp(
                state.transform.scale + (event.deltaY < 0 ? 0.1 : -0.1),
                0.5,
                1.8,
            );

            state.transform.scale = Number(nextScale.toFixed(2));
            applyTransform();
        }, { passive: false });

        viewport.addEventListener('dragover', (event) => {
            event.preventDefault();
        });

        viewport.addEventListener('drop', (event) => {
            event.preventDefault();
            const agentId = Number(event.dataTransfer.getData('text/plain'));
            const point = screenToCanvas(event.clientX, event.clientY);

            addNode(agentId, {
                x: point.x - NODE_WIDTH / 2,
                y: point.y - NODE_HEIGHT / 2,
            });
        });

        clearCanvasButton.addEventListener('click', () => {
            state.nodes = [];
            state.edges = [];
            state.selectedNodeId = null;
            state.connectSource = null;
            render();
        });
    }

    function bindZoom() {
        zoomInButton.addEventListener('click', () => {
            state.transform.scale = clamp(Number((state.transform.scale + 0.1).toFixed(2)), 0.5, 1.8);
            applyTransform();
        });

        zoomOutButton.addEventListener('click', () => {
            state.transform.scale = clamp(Number((state.transform.scale - 0.1).toFixed(2)), 0.5, 1.8);
            applyTransform();
        });

        zoomResetButton.addEventListener('click', () => {
            state.transform.scale = 1;
            state.transform.x = 0;
            state.transform.y = 0;
            applyTransform();
        });
    }

    function bindDeleteModal() {
        deleteCancelButton.addEventListener('click', () => closeDeleteModal());
        deleteModal.addEventListener('click', (event) => {
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        });

        deleteConfirmButton.addEventListener('click', () => {
            if (!state.pendingDeleteNodeId) {
                return;
            }

            const nodeId = state.pendingDeleteNodeId;

            state.nodes = state.nodes.filter((item) => item.id !== nodeId);
            state.edges = state.edges.filter((edge) => edge.from !== nodeId && edge.to !== nodeId);

            if (state.selectedNodeId === nodeId) {
                state.selectedNodeId = null;
            }

            if (state.connectSource?.nodeId === nodeId) {
                state.connectSource = null;
            }

            closeDeleteModal();
            render();
        });
    }

    function bindEditor() {
        const fields = selectedPanel.querySelectorAll('[data-step-field]');

        fields.forEach((field) => {
            field.addEventListener('input', () => {
                const node = getSelectedNode();

                if (!node) return;

                const fieldName = field.dataset.stepField;
                node[fieldName] = field.value;
                render();
            });
        });

        removeStepButton.addEventListener('click', () => {
            const node = getSelectedNode();

            if (!node) return;

            openDeleteModal(node.id, node.title);
        });

        saveForm.addEventListener('submit', (event) => {
            try {
                const definition = buildDefinition();
                hiddenDefinition.value = JSON.stringify(definition, null, 2);

                if (state.nodes.length === 0) {
                    throw new Error('Adicione pelo menos um agente ao canvas.');
                }

                if (!workflowName.value.trim() || !workflowSlug.value.trim()) {
                    throw new Error('Nome e slug do workflow são obrigatórios.');
                }
            } catch (error) {
                event.preventDefault();
                showToast(error.message, 'error');
            }
        });
    }

    function loadWorkflow(workflow) {
        if (!workflow) {
            applyTransform();
            return;
        }

        formTitle.textContent = `Editando workflow #${workflow.id}`;
        saveForm.action = `/workflows/${workflow.id}`;
        saveForm.querySelector('input[name="_method"]').value = 'PUT';
        workflowName.value = workflow.name ?? '';
        workflowSlug.value = workflow.slug ?? '';
        workflowDescription.value = workflow.description ?? '';
        workflowTeam.value = workflow.agent_team_id ?? '';
        workflowActive.checked = !!workflow.is_active;

        const editorNodes = workflow.definition?.editor?.nodes ?? [];
        const editorEdges = workflow.definition?.editor?.edges ?? [];
        const steps = workflow.definition?.steps ?? [];

        state.nodes = steps.map((step, index) => {
            const editorNode = editorNodes.find((item) => item.key === (step.key ?? `step_${index + 1}`));
            const agent = state.agents.find((item) => item.id === step.agent_id);

            return {
                id: crypto.randomUUID(),
                key: step.key ?? `step_${index + 1}`,
                agent_id: step.agent_id,
                title: agent?.name ?? `Agente ${step.agent_id}`,
                role: agent?.role ?? '',
                prompt: step.prompt ?? '',
                system_prompt: step.system_prompt ?? '',
                save_as: step.save_as ?? step.key ?? `step_${index + 1}`,
                output_format: step.output_format ?? 'text',
                x: editorNode?.x ?? 80 + index * 240,
                y: editorNode?.y ?? 100,
            };
        });

        state.edges = [];

        if (editorEdges.length > 0) {
            state.edges = editorEdges.map((edge) => {
                const fromNode = state.nodes.find((node) => node.key === edge.from);
                const toNode = state.nodes.find((node) => node.key === edge.to);

                if (!fromNode || !toNode) {
                    return null;
                }

                return {
                    id: crypto.randomUUID(),
                    from: fromNode.id,
                    to: toNode.id,
                    fromSide: edge.fromSide ?? 'right',
                    toSide: edge.toSide ?? 'left',
                };
            }).filter(Boolean);
        }

        if (state.edges.length === 0) {
            state.edges = state.nodes.slice(1).map((node, index) => ({
                id: crypto.randomUUID(),
                from: state.nodes[index].id,
                to: node.id,
                fromSide: 'right',
                toSide: 'left',
            }));
        }

        state.nextNodeIndex = state.nodes.length + 1;
        applyTransform();
    }

    function addNode(agentId, position) {
        const agent = state.agents.find((item) => item.id === agentId);

        if (!agent) return;

        const index = state.nextNodeIndex++;

        state.nodes.push({
            id: crypto.randomUUID(),
            key: `${agent.slug.replace(/[^a-z0-9]+/gi, '_')}_${index}`,
            agent_id: agent.id,
            title: agent.name,
            role: agent.role ?? '',
            prompt: `Execute sua tarefa com base no contexto atual: {{input}}`,
            system_prompt: '',
            save_as: `${agent.slug.replace(/[^a-z0-9]+/gi, '_')}_result_${index}`,
            output_format: 'text',
            x: clamp(position.x, 16, canvas.clientWidth - NODE_WIDTH - 16),
            y: clamp(position.y, 16, canvas.clientHeight - NODE_HEIGHT - 16),
        });

        state.selectedNodeId = state.nodes[state.nodes.length - 1].id;
        render();
    }

    function render() {
        renderNodes();
        renderEdges();
        renderSelectedNode();
        updateEmptyState();
        updateConnectHint();
        applyTransform();
    }

    function renderNodes() {
        nodesLayer.innerHTML = '';

        state.nodes.forEach((node) => {
            const element = document.createElement('article');
            element.className = `builder-node ${state.selectedNodeId === node.id ? 'is-selected' : ''} ${state.connectSource?.nodeId === node.id ? 'is-connecting' : ''}`;
            element.style.left = `${node.x}px`;
            element.style.top = `${node.y}px`;
            element.dataset.nodeId = node.id;

            element.innerHTML = `
                ${renderPorts('in')}
                <div class="builder-node-card">
                    <div class="builder-node-head" data-drag-handle>
                        <div>
                            <p class="builder-node-title">${escapeHtml(node.title)}</p>
                            <p class="builder-node-meta">${escapeHtml(node.key)}</p>
                        </div>
                    </div>
                    <div class="builder-node-actions">
                        <button type="button" class="builder-node-view" data-select-node>Visualizar</button>
                    </div>
                </div>
                ${renderPorts('out')}
            `;

            element.querySelector('[data-select-node]').addEventListener('click', () => {
                state.selectedNodeId = node.id;
                render();
            });

            element.querySelectorAll('[data-port="out"]').forEach((port) => {
                port.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const side = port.dataset.side;

                    if (state.connectSource?.nodeId === node.id && state.connectSource?.side === side) {
                        state.connectSource = null;
                    } else {
                        state.connectSource = {
                            nodeId: node.id,
                            side,
                        };
                    }

                    render();
                });
            });

            element.querySelectorAll('[data-port="in"]').forEach((port) => {
                port.addEventListener('click', (event) => {
                    event.stopPropagation();

                    if (!state.connectSource || state.connectSource.nodeId === node.id) {
                        return;
                    }

                    connectNodes(state.connectSource.nodeId, node.id, state.connectSource.side, port.dataset.side);
                });
            });

            const handle = element.querySelector('[data-drag-handle]');

            handle.addEventListener('mousedown', (event) => {
                if (event.button !== 0) {
                    return;
                }

                event.preventDefault();
                const point = screenToCanvas(event.clientX, event.clientY);
                state.dragNodeId = node.id;
                state.dragOffsetX = point.x - node.x;
                state.dragOffsetY = point.y - node.y;
            });

            element.addEventListener('contextmenu', (event) => {
                event.preventDefault();
                openDeleteModal(node.id, node.title);
            });

            nodesLayer.appendChild(element);
        });
    }

    function renderEdges() {
        edgesLayer.innerHTML = '';
        edgesLayer.setAttribute('viewBox', `0 0 ${canvas.clientWidth} ${canvas.clientHeight}`);

        state.edges.forEach((edge) => {
            const from = state.nodes.find((item) => item.id === edge.from);
            const to = state.nodes.find((item) => item.id === edge.to);

            if (!from || !to) return;

            const start = getAnchorPoint(from, edge.fromSide ?? 'right', NODE_WIDTH, NODE_HEIGHT);
            const end = getAnchorPoint(to, edge.toSide ?? 'left', NODE_WIDTH, NODE_HEIGHT);
            const curve = getCurveControl(start, end, edge.fromSide ?? 'right', edge.toSide ?? 'left');
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');

            path.setAttribute('d', `M ${start.x} ${start.y} C ${curve.c1x} ${curve.c1y}, ${curve.c2x} ${curve.c2y}, ${end.x} ${end.y}`);
            path.setAttribute('class', 'builder-edge');
            edgesLayer.appendChild(path);
        });
    }

    function renderSelectedNode() {
        const node = getSelectedNode();

        if (!node) {
            selectedPanel.classList.add('hidden');
            selectedHint.classList.remove('hidden');
            return;
        }

        selectedPanel.classList.remove('hidden');
        selectedHint.classList.add('hidden');

        selectedPanel.querySelector('[data-step-field="key"]').value = node.key ?? '';
        selectedPanel.querySelector('[data-step-field="save_as"]').value = node.save_as ?? '';
        selectedPanel.querySelector('[data-step-field="output_format"]').value = node.output_format ?? 'text';
        selectedPanel.querySelector('[data-step-field="prompt"]').value = node.prompt ?? '';
        selectedPanel.querySelector('[data-step-field="system_prompt"]').value = node.system_prompt ?? '';
        selectedPanel.querySelector('[data-selected-title]').textContent = node.title;
    }

    function updateEmptyState() {
        emptyState.classList.toggle('hidden', state.nodes.length > 0);
    }

    function updateConnectHint() {
        if (!state.connectSource) {
            connectHint.textContent = 'Nenhuma conexão em andamento.';
            return;
        }

        const node = state.nodes.find((item) => item.id === state.connectSource.nodeId);
        connectHint.textContent = `Conectando saída ${state.connectSource.side} de ${node?.title ?? 'agente'}. Escolha a entrada do próximo nó.`;
    }

    function connectNodes(fromId, toId, fromSide, toSide) {
        if (state.edges.some((edge) => edge.to === toId)) {
            showToast('Cada passo pode receber apenas uma conexão de entrada.', 'error');
            return;
        }

        if (state.edges.some((edge) => edge.from === fromId)) {
            showToast('Cada passo pode ter apenas uma saída no builder linear.', 'error');
            return;
        }

        if (createsCycle(fromId, toId)) {
            showToast('Essa ligação criaria um ciclo no workflow.', 'error');
            return;
        }

        state.edges.push({
            id: crypto.randomUUID(),
            from: fromId,
            to: toId,
            fromSide,
            toSide,
        });

        state.connectSource = null;
        render();
    }

    function buildDefinition() {
        const orderedNodes = orderNodes();

        return {
            steps: orderedNodes.map((node) => ({
                key: node.key,
                agent_id: node.agent_id,
                prompt: node.prompt,
                system_prompt: node.system_prompt || null,
                save_as: node.save_as,
                output_format: node.output_format,
            })),
            editor: {
                nodes: state.nodes.map((node) => ({
                    key: node.key,
                    x: Math.round(node.x),
                    y: Math.round(node.y),
                })),
                edges: state.edges.map((edge) => ({
                    from: state.nodes.find((node) => node.id === edge.from)?.key,
                    to: state.nodes.find((node) => node.id === edge.to)?.key,
                    fromSide: edge.fromSide,
                    toSide: edge.toSide,
                })),
                viewport: {
                    scale: state.transform.scale,
                    x: Math.round(state.transform.x),
                    y: Math.round(state.transform.y),
                },
            },
        };
    }

    function orderNodes() {
        const incoming = new Map();
        const outgoing = new Map();

        state.nodes.forEach((node) => {
            incoming.set(node.id, 0);
            outgoing.set(node.id, []);
        });

        state.edges.forEach((edge) => {
            incoming.set(edge.to, (incoming.get(edge.to) ?? 0) + 1);
            outgoing.get(edge.from).push(edge.to);
        });

        if (state.edges.length !== Math.max(0, state.nodes.length - 1)) {
            throw new Error('O builder atual exige um fluxo linear: conecte todos os passos em uma única sequência.');
        }

        const startNodes = state.nodes.filter((node) => (incoming.get(node.id) ?? 0) === 0);

        if (startNodes.length !== 1) {
            throw new Error('O workflow precisa ter exatamente um passo inicial.');
        }

        const ordered = [];
        let current = startNodes[0];
        const visited = new Set();

        while (current) {
            if (visited.has(current.id)) {
                throw new Error('O workflow possui ciclo. Remova as conexões inválidas.');
            }

            ordered.push(current);
            visited.add(current.id);

            const nextIds = outgoing.get(current.id) ?? [];

            if (nextIds.length > 1) {
                throw new Error('O builder atual não suporta branching. Deixe apenas uma saída por passo.');
            }

            current = state.nodes.find((node) => node.id === nextIds[0]) ?? null;
        }

        if (ordered.length !== state.nodes.length) {
            throw new Error('Há passos soltos no canvas. Conecte todos antes de salvar.');
        }

        return ordered;
    }

    function createsCycle(fromId, toId) {
        let cursor = fromId;

        if (fromId === toId) {
            return true;
        }

        while (true) {
            const nextEdge = state.edges.find((edge) => edge.to === cursor);

            if (!nextEdge) {
                return false;
            }

            cursor = nextEdge.from;

            if (cursor === toId) {
                return true;
            }
        }
    }

    function getSelectedNode() {
        return state.nodes.find((node) => node.id === state.selectedNodeId) ?? null;
    }

    function openDeleteModal(nodeId, nodeTitle) {
        state.pendingDeleteNodeId = nodeId;
        deleteModalText.textContent = `Remover "${nodeTitle}" do canvas? As conexões ligadas a ele também serão removidas.`;
        deleteModal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        state.pendingDeleteNodeId = null;
        deleteModal.classList.add('hidden');
    }

    function applyTransform() {
        canvas.style.transform = `translate(${state.transform.x}px, ${state.transform.y}px) scale(${state.transform.scale})`;
        zoomLabel.textContent = `${Math.round(state.transform.scale * 100)}%`;
    }

    function screenToCanvas(clientX, clientY) {
        const rect = viewport.getBoundingClientRect();
        return {
            x: (clientX - rect.left - state.transform.x) / state.transform.scale,
            y: (clientY - rect.top - state.transform.y) / state.transform.scale,
        };
    }
}

function renderPorts(type) {
    return ['top', 'right', 'bottom', 'left']
        .map((side) => `<button type="button" class="builder-port builder-port-${type} builder-port-${side}" data-port="${type}" data-side="${side}" title="${type === 'out' ? 'Origem' : 'Destino'}"></button>`)
        .join('');
}

function getAnchorPoint(node, side, width, height) {
    switch (side) {
        case 'top':
            return { x: node.x + width / 2, y: node.y };
        case 'bottom':
            return { x: node.x + width / 2, y: node.y + height };
        case 'left':
            return { x: node.x, y: node.y + height / 2 };
        case 'right':
        default:
            return { x: node.x + width, y: node.y + height / 2 };
    }
}

function getCurveControl(start, end, fromSide, toSide) {
    const distanceX = end.x - start.x;
    const distanceY = end.y - start.y;
    const horizontal = Math.max(56, Math.abs(distanceX) * 0.45);
    const vertical = Math.max(56, Math.abs(distanceY) * 0.45);
    const c1 = { x: start.x, y: start.y };
    const c2 = { x: end.x, y: end.y };

    if (fromSide === 'left') c1.x -= horizontal;
    if (fromSide === 'right') c1.x += horizontal;
    if (fromSide === 'top') c1.y -= vertical;
    if (fromSide === 'bottom') c1.y += vertical;

    if (toSide === 'left') c2.x -= horizontal;
    if (toSide === 'right') c2.x += horizontal;
    if (toSide === 'top') c2.y -= vertical;
    if (toSide === 'bottom') c2.y += vertical;

    return {
        c1x: c1.x,
        c1y: c1.y,
        c2x: c2.x,
        c2y: c2.y,
    };
}

function clamp(value, min, max) {
    return Math.min(Math.max(value, min), max);
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `builder-toast builder-toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('is-visible'));

    setTimeout(() => {
        toast.classList.remove('is-visible');
        setTimeout(() => toast.remove(), 180);
    }, 2400);
}
